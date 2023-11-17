import './bootstrap'
import '../css/app.css'

import { createInertiaApp } from '@inertiajs/react'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createRoot } from 'react-dom/client'

// @ts-expect-error
const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  // @ts-expect-error
  resolve: async (name) => await resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
  setup ({ el, App, props }) {
    createRoot(el).render(<App {...props} />)
  }
})
