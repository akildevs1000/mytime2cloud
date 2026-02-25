import React, { useEffect, useState } from "react";

export function RadiusSlider({
  min = 50,
  max = 1000,
  defaultValue = 250,
  value,
  step = 10,
  onChange = () => { },
}) {
  const [radius, setRadius] = useState(defaultValue);

  // if parent is controlling value, sync internal state
  useEffect(() => {
    if (typeof value === "number" && value !== radius) {
      setRadius(value);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value]);

  // send value to parent when changed
  useEffect(() => {
    onChange?.(radius);
  }, [radius, onChange]);

  return (
    <div>
      <label className="text-xs font-bold text-slate-500 block mb-1.5 uppercase">
        Radius (Meters)
      </label>

      <div className="flex items-center gap-4">
        <input
          type="range"
          min={min}
          max={max}
          step={step}
          value={radius}
          onChange={(e) => setRadius(Number(e.target.value))}
          className="flex-1 accent-primary cursor-pointer"
          aria-label="Radius in meters"
        />

        <span className="text-sm font-black w-14 text-right">{radius}m</span>
      </div>

      {/* optional: hidden input for form submission */}
      <input type="hidden" name="radius" value={radius} />
    </div>
  );
}
