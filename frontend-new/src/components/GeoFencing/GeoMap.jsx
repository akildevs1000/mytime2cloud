import React, { useEffect, useRef } from "react";

function loadGoogleMaps(apiKey) {
  if (typeof window === "undefined") return Promise.reject();
  if (window.google && window.google.maps) return Promise.resolve(window.google.maps);

  return new Promise((resolve, reject) => {
    const existing = document.getElementById("gmaps-script");
    if (existing) {
      existing.addEventListener("load", () => resolve(window.google.maps));
      existing.addEventListener("error", reject);
      return;
    }

    const script = document.createElement("script");
    script.id = "gmaps-script";
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}`;
    script.async = true;
    script.defer = true;
    script.onload = () => resolve(window.google.maps);
    script.onerror = reject;
    document.head.appendChild(script);
  });
}

export default function GeoMap({ center = { lat: 25.2048, lng: 55.2708 }, radius = 150 }) {
  const ref = useRef(null);
  const mapRef = useRef(null);
  const circleRef = useRef(null);
  const markerRef = useRef(null);

  useEffect(() => {
    const apiKey = process.env.NEXT_PUBLIC_GOOGLE_MAPS_API_KEY || "";
    if (!apiKey) {
      console.warn("GeoMap: NEXT_PUBLIC_GOOGLE_MAPS_API_KEY not set");
      return;
    }

    let mounted = true;

    loadGoogleMaps(apiKey)
      .then((maps) => {
        if (!mounted) return;

        if (!mapRef.current) {
          mapRef.current = new maps.Map(ref.current, {
            center,
            zoom: 13,
            disableDefaultUI: true,
          });

          markerRef.current = new maps.Marker({
            position: center,
            map: mapRef.current,
            clickable: false,
            optimized: false,
          });

          circleRef.current = new maps.Circle({
            strokeColor: "#facc15",
            strokeOpacity: 0.9,
            strokeWeight: 2,
            fillColor: "rgba(250,204,21,0.12)",
            fillOpacity: 0.6,
            map: mapRef.current,
            center,
            radius,
          });
        }
      })
      .catch((err) => console.error("Failed to load Google Maps", err));

    return () => {
      mounted = false;
    };
  }, []);

  // update center smoothly
  useEffect(() => {
    const maps = window.google?.maps;
    if (!maps || !mapRef.current) return;

    const latLng = new maps.LatLng(center.lat, center.lng);
    mapRef.current.panTo(latLng);

    if (markerRef.current) markerRef.current.setPosition(latLng);
    if (circleRef.current) circleRef.current.setCenter(latLng);
  }, [center]);

  // update radius
  useEffect(() => {
    if (circleRef.current) {
      circleRef.current.setRadius(Number(radius) || 0);
    }
  }, [radius]);

  return <div ref={ref} className="w-full h-full" />;
}
