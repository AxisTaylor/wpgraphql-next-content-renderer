import { NextConfig } from 'next';

type WPConfig = {
  wpDomain: string;
  wpProtocol: string;
  frontendDomain: string;
  frontendProtocol: string;
}

type OptionalWPConfig = {
  wpHomeUrl: string;
  wpSiteUrl: string;
};

export function withWCR(config: NextConfig, wpConfig: WPConfig & Partial<OptionalWPConfig>) {
  let {
    wpDomain,
    wpProtocol,
    wpHomeUrl,
    wpSiteUrl,
    frontendDomain,
    frontendProtocol,
  } = wpConfig;
  wpHomeUrl = wpHomeUrl || `${wpProtocol}://${wpDomain}`;
  wpSiteUrl = wpSiteUrl || wpHomeUrl;

  const newConfig = {
    ...config,
    env: {
      ...config.env,
      wcr_wp_domain: wpDomain,
      wcr_wp_homeurl: wpHomeUrl,
      wcr_wp_siteurl: wpSiteUrl,
      wcr_frontend_domain: frontendDomain,
      wcr_frontend_url: `${frontendProtocol}://${frontendDomain}`,
    },
    redirects: async () => {
      const wordpressContentRenderRedirects =  [
        {
          source: '/',
          has: [
            {
              type: "query" as "query",
              key: 'wc-ajax',
            },
          ],
          permanent: false,
          destination: '/api/wc',
        },
        {
          source: '/',
          has: [
            {
              type: "query" as "query",
              key: 'wp-ajax',
            },
          ],
          permanent: false,
          destination: '/api/wp',
        },
        {
          source: '/wp-json/:path*',
          permanent: false,
          destination: '/api/wp-json/:path*',
        },
      ];

      const redirects = await config.redirects?.()
      if (!redirects) {
        return wordpressContentRenderRedirects;
      } else if (Array.isArray(redirects)) {
        return redirects.concat(wordpressContentRenderRedirects)
      } else {
        throw new Error("Invalid return type for NextJS configuration redirects callback");
      }
    }
  };

  

  return newConfig;
}
