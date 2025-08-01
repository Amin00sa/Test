// Cache simplifié
const imageCache = new Set(); // Plus léger que Map pour ce cas

function initImages() {
  document.querySelectorAll('img').forEach(img => {
    if (img.complete) { 
      // Si déjà chargée (lazy loading HTML)
      imageCache.add(img.src);
    } else {
      if (imageCache.has(img.src)) {
        img.src = img.src; // Force l'utilisation du cache
      } else {
        img.onload = () => imageCache.add(img.src);
      }
    }
  });
}

// Démarrer au bon moment
if (document.readyState === 'complete') {
  initImages();
} else {
  document.addEventListener('DOMContentLoaded', initImages);
  window.addEventListener('load', initImages);
}
