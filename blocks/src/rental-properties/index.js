( function ( wp ) {
    var registerBlockType = wp.blocks.registerBlockType;
    var el = wp.element.createElement;
    var ServerSideRender = wp.serverSideRender && wp.serverSideRender.ServerSideRender ? wp.serverSideRender.ServerSideRender : wp.serverSideRender || wp.ServerSideRender || null;

    registerBlockType( 'syn-ownd-child/lc-rental-properties', {
        edit: function ( props ) {
            if ( ServerSideRender ) {
                return el( ServerSideRender, { block: 'syn-ownd-child/lc-rental-properties', attributes: props.attributes } );
            }
            return el( 'div', { className: 'lc-block-placeholder' }, '賃貸物件ブロック' );
        },
        save: function () { return null; }
    } );
} )( window.wp );
