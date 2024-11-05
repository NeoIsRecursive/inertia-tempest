import { createInertiaApp } from "@inertiajs/react";
import { createRoot } from "react-dom/client";

type InertiaPage = {
  default: ((props: unknown) => JSX.Element) & {
    layout?: (page: JSX.Element) => JSX.Element;
  };
};

createInertiaApp({
  resolve: async (name) => {
    // If you are using vite:
    // const pages = import.meta.glob<InertiaPage>("./pages/**/*.tsx", {
    //   eager: true,
    // });
    //
    // const page = pages[`./pages/${name}.tsx`];

    const page = (await import(`./pages/${name}.tsx`)) as InertiaPage;

    if (!page) {
      throw new Error(`Page not found: ${name}`);
    }

    return page;
  },
  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />);
  },
});
