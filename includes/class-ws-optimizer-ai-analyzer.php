<?php

defined( 'ABSPATH' ) || exit;

class WS_Optimizer_AI_Analyzer {

    const DEFAULT_MODEL      = 'claude-opus-4-6';
    const DEFAULT_MAX_TOKENS = 800;

    private $model;
    private $max_tokens;

    public function __construct( $model = self::DEFAULT_MODEL, $max_tokens = self::DEFAULT_MAX_TOKENS ) {
        $this->model      = $model;
        $this->max_tokens = (int) $max_tokens;
    }

    /**
     * Point d'entrée principal. Analyse un titre SEO via WordPress AI Client.
     *
     * @param string   $title   Titre à analyser.
     * @param int|null $post_id Optionnel — enrichit l'analyse avec keyword focus + URL.
     * @return array  ['success' => true, 'data' => [...]] ou ['error' => 'message']
     */
    public function analyze_title( $title, $post_id = null ) {
        if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
            return [
                'error' => __( 'WordPress AI Client requis (WordPress 7.0+).', 'ws-optimizer-ai' ),
            ];
        }

        if ( '' === trim( $title ) ) {
            return [
                'error' => __( 'Le titre est vide.', 'ws-optimizer-ai' ),
            ];
        }

        $basic_data = $this->build_basic_data( $title, $post_id );
        $prompt     = $this->build_prompt( $basic_data );

        return $this->call_claude( $prompt );
    }

    /**
     * Construit le tableau de métriques brutes du titre.
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
            $keyword = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true )
                    ?: get_post_meta( $post_id, '_rank_math_focus_keyword', true );
            $data['focus_keyword'] = $keyword ?: __( 'Non définie', 'ws-optimizer-ai' );
            $data['url']           = get_permalink( $post_id );
        }

        return $data;
    }

    /**
     * Construit le prompt envoyé à Claude.
     */
    public function build_prompt( array $basic_data ) {
        return "Analyse ce titre d'article SEO et donne-moi un verdict structuré EN JSON.\n\n" .
               "Données du titre :\n" .
               wp_json_encode( $basic_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) .
               "\n\n" .
               "Évalue sur ces critères :\n" .
               "- Longueur (optimal 50-60 caractères)\n" .
               "- Présence et position de la keyword\n" .
               "- Utilisation de mots-puissance (meilleur, guide, complet, etc.)\n" .
               "- Patterns performants (chiffres, parenthèses, questions)\n" .
               "- Lisibilité et clarté\n\n" .
               "Retourne UNIQUEMENT du JSON (aucune explication, aucun markdown) avec cette structure exacte :\n" .
               "{\n" .
               "  \"score\": nombre entre 0 et 100,\n" .
               "  \"verdict\": \"string court (emoji + texte)\",\n" .
               "  \"strengths\": [\"point fort 1\", \"point fort 2\"],\n" .
               "  \"issues\": [{\"severity\": \"critical/warning\", \"message\": \"...\"}, ...],\n" .
               "  \"recommendations\": [\"action 1\", \"action 2\"],\n" .
               "  \"analysis\": \"1-2 phrases expliquant le verdict\"\n" .
               "}";
    }

    /**
     * Appelle WordPress AI Client (wp_ai_client_prompt).
     */
    public function call_claude( $prompt ) {
        try {
            $response = wp_ai_client_prompt(
                'Anthropic',
                'text',
                [
                    'prompt'     => $prompt,
                    'model'      => $this->model,
                    'max_tokens' => $this->max_tokens,
                ]
            );

            return $this->parse_response( $response );
        } catch ( Exception $e ) {
            return [
                'error' => sprintf(
                    /* translators: %s: error message */
                    __( 'Erreur lors de l\'appel à Claude : %s', 'ws-optimizer-ai' ),
                    $e->getMessage()
                ),
            ];
        }
    }

    /**
     * Parse la réponse brute de wp_ai_client_prompt.
     */
    public function parse_response( $response ) {
        $text = '';

        if ( is_array( $response ) && isset( $response['content'][0]['text'] ) ) {
            $text = $response['content'][0]['text'];
        } elseif ( is_string( $response ) ) {
            $text = $response;
        }

        // Supprimer les backticks markdown éventuels
        $text = preg_replace( '/^```json\s*|\s*```$/m', '', trim( $text ) );

        $analysis = json_decode( $text, true );

        if ( ! $analysis || ! is_array( $analysis ) ) {
            return [
                'error' => __( 'Réponse Claude invalide.', 'ws-optimizer-ai' ),
                'raw'   => $text,
            ];
        }

        return [
            'success' => true,
            'data'    => $analysis,
        ];
    }

    public function get_model()      { return $this->model; }
    public function get_max_tokens() { return $this->max_tokens; }
}
