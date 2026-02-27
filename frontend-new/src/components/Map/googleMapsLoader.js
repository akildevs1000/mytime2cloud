export function loadGoogleMaps(apiKey) {
  if (typeof window === "undefined") return Promise.reject();
  if (window.google && window.google.maps) return Promise.resolve(window.google.maps);

  return new Promise((resolve, reject) => {
    const existing = document.getElementById("gmaps-script");
    if (existing) {
      if (window.google && window.google.maps) {
        resolve(window.google.maps);
        return;
      }

      const onLoad = () => {
        if (window.google && window.google.maps) {
          resolve(window.google.maps);
        } else {
          reject(new Error("Google Maps loaded but window.google.maps is unavailable"));
        }
      };

      const onError = () => reject(new Error("Failed to load Google Maps script"));

      existing.addEventListener("load", onLoad, { once: true });
      existing.addEventListener("error", onError, { once: true });

      const pollId = setInterval(() => {
        if (window.google && window.google.maps) {
          clearInterval(pollId);
          resolve(window.google.maps);
        }
      }, 200);

      setTimeout(() => {
        clearInterval(pollId);
      }, 5000);

      return;
    }

    const script = document.createElement("script");
    script.id = "gmaps-script";
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}`;
    script.async = true;
    script.defer = true;
    script.onload = () => {
      if (window.google && window.google.maps) {
        resolve(window.google.maps);
      } else {
        reject(new Error("Google Maps loaded but window.google.maps is unavailable"));
      }
    };
    script.onerror = () => reject(new Error("Failed to load Google Maps script"));
    document.head.appendChild(script);
  });
}