/* global wsoaData, wp */
(function () {
    'use strict';

    var d = window.wsoaData || {};

    /** Récupère le titre courant (Gutenberg ou Classic Editor). */
    function getTitle() {
        try {
            var win = window.parent || window;
            if ( win.wp && win.wp.data && win.wp.data.select ) {
                var title = win.wp.data.select( 'core/editor' ).getEditedPostAttribute( 'title' );
                if ( title ) return title.trim();
            }
        } catch ( e ) { /* cross-origin ou pas de parent */ }

        var field = document.getElementById( 'title' );
        if ( field ) return field.value.trim();

        try {
            var pField = window.parent.document.getElementById( 'title' );
            if ( pField ) return pField.value.trim();
        } catch ( e ) { /* noop */ }

        return '';
    }

    function escHtml( str ) {
        var div = document.createElement( 'div' );
        div.appendChild( document.createTextNode( String( str ) ) );
        return div.innerHTML;
    }

    function t( key ) {
        return ( d.i18n && d.i18n[ key ] ) ? d.i18n[ key ] : key;
    }

    function scoreClass( score ) {
        if ( score >= 80 ) return 'wsoa-score--ok';
        if ( score >= 60 ) return 'wsoa-score--warn';
        return 'wsoa-score--err';
    }

    /** Construit le contenu interne de #wsoa-result-<id> — même structure que le rendu PHP. */
    function buildResultHtml( analysis ) {
        var score = parseInt( analysis.score, 10 ) || 0;
        var html  = '';

        html += '<div class="wsoa-score ' + scoreClass( score ) + '">';
        html += '<span class="wsoa-score__value">' + escHtml( score ) + '</span>';
        html += '<span class="wsoa-score__max">/100</span></div>';

        if ( analysis.verdict ) {
            html += '<p class="wsoa-verdict">' + escHtml( analysis.verdict ) + '</p>';
        }

        if ( analysis.analysis ) {
            html += '<p class="wsoa-analysis">' + escHtml( analysis.analysis ) + '</p>';
        }

        if ( analysis.strengths && analysis.strengths.length ) {
            html += '<div class="wsoa-section wsoa-section--ok"><strong>' + escHtml( t( 'Atouts' ) ) + '</strong><ul>';
            analysis.strengths.forEach( function ( s ) {
                html += '<li>' + escHtml( s ) + '</li>';
            } );
            html += '</ul></div>';
        }

        if ( analysis.issues && analysis.issues.length ) {
            html += '<div class="wsoa-section wsoa-section--err"><strong>' + escHtml( t( 'Problèmes' ) ) + '</strong><ul>';
            analysis.issues.forEach( function ( issue ) {
                var icon = issue.severity === 'critical' ? '🚨' : '⚠️';
                html += '<li>' + icon + ' ' + escHtml( issue.message || '' ) + '</li>';
            } );
            html += '</ul></div>';
        }

        if ( analysis.recommendations && analysis.recommendations.length ) {
            html += '<div class="wsoa-section wsoa-section--tip"><strong>' + escHtml( t( 'Recommandations' ) ) + '</strong><ul>';
            analysis.recommendations.forEach( function ( r ) {
                html += '<li>' + escHtml( r ) + '</li>';
            } );
            html += '</ul></div>';
        }

        return html;
    }

    function bindButton( btn ) {
        btn.addEventListener( 'click', function () {
            var postId   = btn.dataset.postId || 0;
            var spinner  = btn.parentElement ? btn.parentElement.querySelector( '.wsoa-spinner' ) : null;
            var resultEl = document.getElementById( 'wsoa-result-' + postId );

            var title = getTitle();
            if ( ! title ) {
                alert( t( 'noTitle' ) );
                return;
            }

            btn.disabled    = true;
            btn.textContent = t( 'analyzing' );
            if ( spinner ) spinner.style.display = 'inline-block';

            var form = new FormData();
            form.append( 'action',  'wsoa_analyze' );
            form.append( 'nonce',   d.nonce || '' );
            form.append( 'title',   title );
            form.append( 'post_id', postId );

            fetch( d.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body: form } )
                .then( function ( r ) {
                    if ( ! r.ok ) {
                        console.error( '[WS SEO Title AI] AJAX HTTP error: ' + r.status );
                        throw new Error( 'HTTP ' + r.status );
                    }
                    return r.json();
                } )
                .then( function ( res ) {
                    if ( res.success ) {
                        if ( resultEl ) {
                            resultEl.classList.remove( 'wsoa-result--empty' );
                            resultEl.innerHTML = buildResultHtml( res.data );
                        }
                        btn.textContent = t( 'reanalyze' );
                    } else {
                        var msg = ( res.data && res.data.message ) ? res.data.message : t( 'error' );
                        if ( resultEl ) resultEl.innerHTML = '<p class="wsoa-error">' + escHtml( msg ) + '</p>';
                        btn.textContent = t( 'analyze' );
                    }
                } )
                .catch( function ( err ) {
                    console.error( '[WS SEO Title AI] AJAX error:', err );
                    if ( resultEl ) resultEl.innerHTML = '<p class="wsoa-error">' + escHtml( t( 'error' ) ) + '</p>';
                    btn.textContent = t( 'analyze' );
                } )
                .finally( function () {
                    btn.disabled = false;
                    if ( spinner ) spinner.style.display = 'none';
                } );
        } );
    }

    document.addEventListener( 'DOMContentLoaded', function () {
        document.querySelectorAll( '.wsoa-btn-analyze' ).forEach( bindButton );
    } );

    // Support lazy rendering (metabox injecté après DOMContentLoaded par Gutenberg).
    if ( window.MutationObserver ) {
        var observer = new MutationObserver( function ( mutations ) {
            mutations.forEach( function ( m ) {
                m.addedNodes.forEach( function ( node ) {
                    if ( node.nodeType !== 1 ) return;
                    var btns = node.classList && node.classList.contains( 'wsoa-btn-analyze' )
                        ? [ node ]
                        : Array.from( node.querySelectorAll( '.wsoa-btn-analyze' ) );
                    btns.forEach( function ( btn ) {
                        if ( ! btn.__wsoa_bound ) {
                            btn.__wsoa_bound = true;
                            bindButton( btn );
                        }
                    } );
                } );
            } );
        } );
        observer.observe( document.body, { childList: true, subtree: true } );
    }
}());
