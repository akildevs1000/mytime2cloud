// @ts-nocheck
"use client";

import { useEffect, useState } from "react";
import LiveTracker from "@/components/Map/Index";


const LiveTrackerPage = () => {
  return (
    <div className="p-4 pb-0 overflow-y-auto max-h-[calc(100vh)]">
      <LiveTracker />
    </div>
  );
};

export default LiveTrackerPage;
