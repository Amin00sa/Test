async function switchLang(lang) {
  const i18n = this.$i18n

  // 1. Vérifier si la langue existe déjà
  if (i18n.availableLocales.includes(lang)) {
    i18n.locale = lang
    return
  }

  // 2. Sinon, appeler ton backend Symfony
  try {
    const res = await fetch(`/api/translate?lang=${lang}`)
    if (!res.ok) {
      throw new Error(`Erreur API: ${res.status}`)
    }
    const messages = await res.json()

    // 3. Injecter la langue dynamiquement
    i18n.setLocaleMessage(lang, messages)
    i18n.locales.push({ code: lang, name: lang.toUpperCase() })

    // 4. Activer la langue
    i18n.locale = lang
  } catch (err) {
    console.error("Impossible de charger la langue :", err)
  }
}
