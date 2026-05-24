<?php

defined( 'ABSPATH' ) || exit;

class WS_Optimizer_AI_Analyzer {

    const DEFAULT_MODEL      = 'claude-opus-4-6';
    const DEFAULT_MAX_TOKENS = 800;

    private $model;
    private $max_tokens;

    public function __construct( $model = self::DEFAULT_MODEL, $max_tokens = self::DEFAULT_MAX_TOKENS ) {
        $this->model      = $model;
        $this->max_tokens = $max_tokens;
    }

    /**
     * Main entry point. Checks WP AI Client availability, builds data,
     * calls Claude and returns the parsed result.
     *
     * @param string   $title
     * @param int|null $post_id
     * @return array
     */
    public function analyze_title( $title, $post_id = null ) {
        if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
            return [ 'error' => __( 'WordPress AI Client requis (WordPress 7.0+).', 'ws-optimizer-ai' ) ];
        }

        $title = trim( $title );
        if ( '' === $title ) {
            return [ 'error' => __( 'Le titre est vide.', 'ws-optimizer-ai' ) ];
        }

        $basic_data = $this->build_basic_data( $title, $post_id );
        $prompt     = $this->build_prompt( $basic_data );

        return $this->call_claude( $prompt );
    }

    /**
     * Build the data payload describing the title.
     */
    public function build_basic_data( $title, $post_id = null ) {
        $data = [
            'title'        => $title,
            'length'       => strlen( $title ),
            'word_count'   => str_word_count( $title ),
            'has_number'   => (int) preg_match( '/\d+/', $title ),
            'has_modifier' => (int) preg_match( '/[\(\[\{]/', $title ),
        ];

        if ( $post_id ) {
            $keyword           = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true )
                              ?: get_post_meta( $post_id, '_rank_math_focus_keyword', true );
            $data['focus_keyword'] = $keyword ?: __( 'Non définie', 'ws-optimizer-ai' );
            $data['url']           = get_permalink( $post_id );
        }

        return $data;
    }

    /**
     * Build the Claude prompt from the data payload.
     */
    public function build_prompt( array $basic_data ) {
        return "Analyse ce titre d'article SEO et donne-moi un verdict structuré EN JSON.\n\n"
             . "Données du titre:\n"
             . wp_json_encode( $basic_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE )
             . "\n\nÉvalue sur ces critères:\n"
             . "- Longueur (optimal 50-60 caractères)\n"
             . "- Présence et position de la keyword\n"
             . "- Utilisation de mots-puissance (meilleur, guide, complet, etc.)\n"
             . "- Patterns performants (chiffres, parenthèses, questions)\n"
             . "- Lisibilité et clarté\n\n"
             . "Retourne UNIQUEMENT du JSON avec cette structure exacte:\n"
             . "{\n"
             . "  \"score\": nombre entre 0 et 100,\n"
             . "  \"verdict\": \"string court (emoji + texte)\",\n"
             . "  \"strengths\": [\"point fort 1\", \"point fort 2\"],\n"
             . "  \"issues\": [{\"severity\": \"critical/warning\", \"message\": \"...\"}, ...],\n"
             . "  \"recommendations\": [\"action 1\", \"action 2\"],\n"
             . "  \"analysis\": \"1-2 phrases expliquant le verdict\"\n"
             . "}";
    }

    /**
     * Extract plain text from wp_ai_client_prompt() response.
     * The function may return an array, an object, or a scalar depending on WP version.
     */
    private function extract_text( $response ) {
        // Array: ['content'][0]['text']
        if ( is_array( $response ) && isset( $response['content'][0]['text'] ) ) {
            return (string) $response['content'][0]['text'];
        }
        // Object: try common methods first
        if ( is_object( $response ) ) {
            if ( method_exists( $response, 'get_text' ) ) {
                return (string) $response->get_text();
            }
            if ( method_exists( $response, 'get_content' ) ) {
                return (string) $response->get_content();
            }
            // Convert to array via JSON and try canonical structure
            $arr = json_decode( wp_json_encode( $response ), true );
            if ( is_array( $arr ) && isset( $arr['content'][0]['text'] ) ) {
                return (string) $arr['content'][0]['text'];
            }
            // Object with direct text/content scalar property
            if ( isset( $response->text ) && is_scalar( $response->text ) ) {
                return (string) $response->text;
            }
            if ( isset( $response->content ) && is_scalar( $response->content ) ) {
                return (string) $response->content;
            }
        }
        // Scalar fallback
        if ( is_scalar( $response ) ) {
            return (string) $response;
        }
        return '';
    }

    /**
     * Parse the raw Claude response into an array.
     */
    public function parse_response( $response ) {
        $text = $this->extract_text( $response );

        // Strip markdown backtick fences if present
        $text = preg_replace( '/^```json\s*|\s*```$/m', '', trim( $text ) );

        $data = json_decode( $text, true );
        if ( ! $data ) {
            return [ 'error' => __( 'Réponse Claude invalide.', 'ws-optimizer-ai' ), 'raw' => $text ];
        }

        return [ 'success' => true, 'data' => $data ];
    }

    /**
     * Call the WordPress AI Client and return the parsed result.
     */
    public function call_claude( $prompt ) {
        try {
            // WordPress AI Client builder pattern — wp_ai_client_prompt($text)
            // returns WP_AI_Client_Prompt_Builder. Chain via __call then generate_text().
            // wp_ai_client_prompt($prompt) returns WP_AI_Client_Prompt_Builder.
            // usingModel() requires a ModelInterface object — cannot pass a string.
            // Provider and model are configured by the user in WP Settings > AI Client.
            $builder = wp_ai_client_prompt( $prompt );
            $this->log_entry( 'builder_received', [
                'type'  => gettype( $builder ),
                'class' => is_object( $builder ) ? get_class( $builder ) : null,
            ] );

            // Trigger the actual API call — provider/model from WP AI Client settings
            $result = $builder->generate_text();

            $this->log_entry( 'generate_text_result', [
                'type'  => gettype( $result ),
                'class' => is_object( $result ) ? get_class( $result ) : null,
                'value' => is_scalar( $result ) ? substr( (string) $result, 0, 300 ) : substr( wp_json_encode( $result ), 0, 300 ),
            ] );

            return $this->parse_response( $result );

        } catch ( \Throwable $e ) {
            // Catch both \Exception and \Error (fatal errors from bad chaining)
            $this->log_entry( 'exception', [
                'class'   => get_class( $e ),
                'message' => $e->getMessage(),
                'file'    => basename( $e->getFile() ),
                'line'    => $e->getLine(),
            ] );
            return [
                'error' => sprintf( __( 'Erreur lors de l\'appel à Claude : %s', 'ws-optimizer-ai' ), $e->getMessage() ),
            ];
        }
    }

    /**
     * Log the raw response from wp_ai_client_prompt() for debugging.
     */
    private function log_response( $response ) {
        $type    = gettype( $response );
        $raw     = '';
        $private = [];
        $props   = [];

        if ( is_object( $response ) ) {
            $methods = get_class_methods( $response );
            $raw     = get_class( $response );

            // Read private/protected properties via Reflection to see inner state
            try {
                $ref = new \ReflectionClass( $response );
                foreach ( $ref->getProperties() as $prop ) {
                    $prop->setAccessible( true );
                    $val = $prop->getValue( $response );
                    $props[ $prop->getName() ] = gettype( $val );
                    if ( is_scalar( $val ) || is_null( $val ) ) {
                        $private[ $prop->getName() ] = $val;
                    } elseif ( is_array( $val ) ) {
                        $private[ $prop->getName() ] = $val;
                    } elseif ( is_object( $val ) ) {
                        $private[ $prop->getName() ] = [
                            '__class'   => get_class( $val ),
                            '__methods' => get_class_methods( $val ),
                            '__cast'    => (array) $val,
                        ];
                    }
                }
            } catch ( \Exception $e ) {
                $private['_reflection_error'] = $e->getMessage();
            }
        } elseif ( is_array( $response ) ) {
            $methods = [];
            $raw     = wp_json_encode( $response );
        } else {
            $methods = [];
            $raw     = (string) $response;
        }

        $text = $this->extract_text( $response );

        $this->log_entry( 'response', [
            'type'           => $type,
            'class'          => is_object( $response ) ? get_class( $response ) : null,
            'methods'        => $methods ?? [],
            'props_types'    => $props,
            'private_values' => $private,
            'raw'            => substr( $raw, 0, 500 ),
            'extracted_text' => substr( $text, 0, 200 ),
        ] );
    }

    /**
     * Append a log entry to the wsoa_debug_log option (last 20 entries).
     */
    public static function log_entry( $type, $data ) {
        $log   = get_option( 'wsoa_debug_log', [] );
        $log[] = [
            'time' => current_time( 'mysql' ),
            'type' => $type,
            'data' => $data,
        ];
        // Keep last 20 entries
        if ( count( $log ) > 20 ) {
            $log = array_slice( $log, -20 );
        }
        update_option( 'wsoa_debug_log', $log );
    }

    // Accessors used by unit tests
    public function get_model()      { return $this->model; }
    public function get_max_tokens() { return $this->max_tokens; }
}
