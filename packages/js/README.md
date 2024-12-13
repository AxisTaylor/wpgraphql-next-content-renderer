# WPGraphQL Next Content Renderer

A series of tools and React components for rendering WordPress content pages in Next.js

## Usage

1. Install with `npm install @axistaylor/wpgraphql-next-content-renderer`
2. Update your `next.config.mjs` file:
  
  ```js
  import { withWCR } from '@axistaylor/wpgraphql-next-content-renderer/withWCR';
  
  /**
   * @type {import('next').NextConfig}
   */
  const nextConfig = {
    env: {
      ...
    }
  };
  
  const wpDomain = 'example.com';
  const wpProtocol = 'https';
  
  export default withWCR(nextConfig, {
    wpDomain,
    wpProtocol,
    wpHomeUrl: `${wpProtocol}://${wpDomain}`,
    wpSiteUrl: `${wpProtocol}://${wpDomain}/wp`, // <-- `/wp` is typically add for some non-traditional WordPress setups like WP Bedrock.
    frontendDomain: 'localhost:3000',
    frontendProtocol: 'http',
  });
  ```

3. Add a `middleware.ts` file:

  ```ts
  export const middleware = async (request: NextRequest) => {
    return await proxyByWCR(request);
  }

  export const config = {
    matcher: [
        '/api/wp',
        '/api/wc',
        '/api/wp-internal-assets/:path*',
        '/api/wp-assets/:path*',
        '/api/wp-json/:path*',
    ],
  }
  ```

4. Now you're to query for and render the content pages:

  ```ts
  // ./lib/utils.ts
  async function fetchContentAndScripts(uri: string) {
    const response = await fetch(process.env.GRAPHQL_ENDPOINT as  string, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        query: `query ($uri: String!) {
          nodeByUri(uri: $uri) {
            ... on ContentNode {
              enqueuedStylesheets(first: 500) {
                nodes {
                  handle
                  src
                  version
                  after
                  before
                }
              }
              enqueuedScripts(first: 500) {
                nodes {
                  handle
                  src
                  strategy
                  version
                  after
                  group
                  location
                  before
                  extraData
                }
              }
            }
            ... on Page {
              content
            }
            ... on Post {
              content
            }
          }
        }`,
        variables: { uri }
      }),
      cache: 'no-store',
    });
  
    const { data } = await response.json();
    const node = data?.nodeByUri;
  
    if (!node) {
      return {
        content: '',
        scripts: [],
        stylesheets: [],
      };
    }
  
    const content = node.content;
    const scripts = node.enqueuedScripts.nodes;
    const stylesheets = node.enqueuedStylesheets.nodes;
  
    return {
      content,
      scripts,
      stylesheets,
    }
  }
  ```

  ```tsx
  // ./components/Styles.tsx
  'use client';

  import { RenderStylesheets, EnqueuedStylesheet } from   "@axistaylor/wpgraphql-next-content-renderer/client";
  
  export default function Styles({ stylesheets }: { stylesheets:   EnqueuedStylesheet[] }) {
    return <RenderStylesheets stylesheets={stylesheets} />;
  }
  ```

  ```tsx
  // ./components/Scripts.tsx
  import type { PropsWithChildren } from 'react';
  import { RenderScripts, EnqueuedScript } from "@axistaylor/  wpgraphql-next-content-renderer";
  
  
  export interface ScriptsProps {
    scripts: EnqueuedScript[];
    type?: 'header' | 'footer';
  }
  
  export default function Scripts({ scripts, type, children }:   PropsWithChildren<ScriptsProps>) {
    return <RenderScripts scripts={scripts} type={type}>{children}</  RenderScripts>;
  }
  ```

  ```tsx
  // ./app/page.tsx
  import { Content } from '@axistaylor/wpgraphql-next-content-renderer';
  
  import { fetchContentAndScripts } from '@/lib/utils';
  import Styles from "@/components/Styles";
  import Scripts from "@/components/Scripts";
  
  export default async function Home() {
    const { content, stylesheets, scripts } = await fetchContentAndScripts('/sample-page');
  
    return (
      <>
        <Styles stylesheets={stylesheets} />
        <Scripts scripts={scripts}>
          <div id="main-content" className="min-h-screen px-4 pt-4">
            <Content content={content} />
          </div>
        </Scripts>
      </>
    );
  }
  ```

### Optimization Recommendation

Some wordpress content pages use a lot of dynamic inline scripts and styles and require the `proxyByWCR` middleware function to work as intended. This has the added effect of making the route dynamic and incapable of being statically generated. To mitigate the potential performance hit, isolate the wordpress content pages to a route group with it's own dedicated root layout. Read more it [here](https://nextjs.org/docs/app/building-your-application/routing/route-groups#creating-multiple-root-layouts).
