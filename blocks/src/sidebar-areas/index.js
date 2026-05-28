( function ( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var ServerSideRender = wp.serverSideRender && wp.serverSideRender.ServerSideRender ? wp.serverSideRender.ServerSideRender : wp.serverSideRender || wp.ServerSideRender || null;

    registerBlockType( 'syn-ownd-child/lc-sidebar-areas', {
        edit: function ( props ) {
            if ( ServerSideRender ) {
                return el( ServerSideRender, { block: 'syn-ownd-child/lc-sidebar-areas', attributes: props.attributes } );
            }
            return el( 'div', { className: 'lc-block-placeholder' }, 'エリアから探す' );
        },
        save: function () { return null; }
    } );
} )( window.wp );
