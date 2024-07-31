// const settings = window.wc.wcSettings.getSetting( 'woocommerce_klump_settings', {} );
// const label = window.wp.htmlEntities.decodeEntities( settings.title ) || window.wp.i18n.__( 'Klump', 'wc-phonepe' );
//
// const Content = () => {
//   return window.wp.htmlEntities.decodeEntities( settings.description || '' );
// };
//
// const Block_Gateway = {
//   name: 'klump',
//   label: label,
//   content: Object( window.wp.element.createElement )( Content, null ),
//   edit: Object( window.wp.element.createElement )( Content, null ),
//   canMakePayment: () => true,
//   ariaLabel: label,
//   supports: {
// 	features: settings.supports,
//   },
// };
//
// window.wc.wcBlocksRegistry.registerPaymentMethod( Block_Gateway );

// Pay in 4 instalments - Klump BNPL (logo)

(() => {
  "use strict";
  const e = window.React,
      t = window.wc.wcBlocksRegistry,
      l = window.wc.wcSettings,
      a = window.wp.i18n,
      i = window.wp.htmlEntities,
      s = (0, a.__)("Klump ", "klump"),
      r = ({ title: e }) => (0, i.decodeEntities)(e) || s,
      o = ({ description: e }) => (0, i.decodeEntities)(e || ""),
      n = ({ logoUrls: t, label: l }) =>
          (0, e.createElement)(
              "div",
              { style: { display: "flex", flexDirection: "row", gap: "0.5rem", flexWrap: "wrap" } },
              t.map((t, a) => (0, e.createElement)("img", { key: a, src: t, alt: l }))
          ),
      c = (0, l.getSetting)("klump_data", {}),
      d = r({ title: c.title }),
      w = {
        name: "klump",
        label: (0, e.createElement)(
            ({ logoUrls: t, title: l }) =>
                (0, e.createElement)(
                    e.Fragment,
                    null,
                    (0, e.createElement)("div", { style: { display: "flex", flexDirection: "row", gap: "0.5rem" } }, (0, e.createElement)("div", null, r({ title: l })), (0, e.createElement)(n, { logoUrls: t, label: r({ title: l }) }))
                ),
            { logoUrls: c.logo_urls, title: d }
        ),
        content: (0, e.createElement)(o, { description: c.description }),
        edit: (0, e.createElement)(o, { description: c.description }),
        canMakePayment: () => true,
        ariaLabel: d,
        supports: { features: c.supports },
      };
  (0, t.registerPaymentMethod)(w);
})();
