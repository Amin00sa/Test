export default function ({ store, route, redirect }) {
  const publicPages = ['/login', '/mfa']
  const isPublic = publicPages.includes(route.path)

  if (!isPublic && !store.state.auth.loggedIn) {
    return redirect('/login')
  }
}
//nuxt config
export default {
  router: {
    middleware: ['auth']
  }
}
