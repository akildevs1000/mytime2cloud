export const employeesSeed = [
  {
    id: 1,
    name: "John Doe",
    role: "Senior Field Engineer",
    location: "West Branch Office",
    status: "active",
    shift: "09:00 - 17:00",
    clockIn: "08:58 AM",
    expectedOut: "05:00 PM",
    battery: 82,
    matchScore: "99.2%",
    checkinTime: "2m ago",
    checkinType: "CHECK-IN",
    verified: true,
    mapPos: { top: "35%", left: "45%" },
    lat: 25.2685,
    lng: 55.2882,
    avatar:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAgOmBDUE9YRPKrUELhubdiGKupJPt-_S1cAy0MCwnS4XLJ0F8HKYFSrehE-s5euFiPpgEgHiFZD1C4_azu015NF6eEUjCMMmf5ddSOmpi7ops0nKsPkh-1dy7Q1O1Pp1zJHGd2YLtIXjenPSPEq1tcWmZihbIU5Lihw_hliby7B7g5OIIOw7sSOcnp6QZ9Kaqnr238I7B2rX5VS7ZLN459F5CuA34Ygdr8rggzQtDdziWsB7Dzre13RYIJcDIEu1yRzWs-3KnWTG0_",
    refPhoto:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuCpuHuPvKJ-OCU_SWCUKbTl9A1bkYIObn45SD6zIRyBPGxh9ozH5rliQZbKaWLnwieFy66QRxJs_rVxJ28X-QNhxfnCFVtp1sRXPST_p6tQZsWVJzFrXcjd9veTIERvC0oH1fzTv-6-askTYufViMnxeJlGipTNkjZgRj1ArZ_cc-7WafzJInZgxuypOW06H3lNN241UqYi-e4OBezv1-AtMkXejLMh0a7R-l4NHajnk7IOsEot82U8HzMsphPWLGTj-bVZbYu29kmW",
    livePhoto:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAB4uboUFzRdHOoKqfS3LX6c5lEvQ4iEMgt_2WI80FOmrymhPfLvnVg3MkIssLQXmr7NAP8zyxLVrhshpEioeGt3gv5nOPDLBrj6HulU-q7b_My2ofalSElnNwRDmPNfDJmXUNsGR71dBEyxgTASi9mA0oFc-aLwaveZzdXZCy9tLMIvCnEARDmiDWzRgDPIhWNarEaq3HnHW3oy7njRPCpWIY2_bV-Jz1Mv560hkzOiInB36okrjyZlbVdvRRUS6qtbHwY9ZO5YXlo",
    feedThumb:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuC5sQ1au3fUOqOakeVlZYmy9oip1UWbBjFiB2_n6cY4RLEHoiPSkaTMuye7gzCkL3lSydkJG-FNf_HNdw6nX4krujcOx_OxZ3yxOxPBQ2WAwBTDeEm3Xy1PXfqTLwk4xTzfgq03Amg8u6Tt_bnCNWfBhD1nQhBxxXIeKRp4ZuayieOA07UWQj0OLvEPwj-vw7N2U3G6GJ7bqrAVXih3kPx5RliPZjS83EylAE4qDk8gu3R-MyRyaaf09E8r-b4wSZnG1XLdBL8rdCox",
  },
  {
    id: 2,
    name: "Sarah Smith",
    role: "Logistics Coordinator",
    location: "North Warehouse",
    status: "active",
    shift: "08:00 - 16:00",
    clockIn: "07:55 AM",
    expectedOut: "04:00 PM",
    battery: 67,
    matchScore: "98.1%",
    checkinTime: "5m ago",
    checkinType: "CHECK-IN",
    verified: true,
    mapPos: { top: "55%", left: "60%" },
    lat: 25.2312,
    lng: 55.3214,
    avatar:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAhS-rjJPilpVNSIf4S1vqyLYBjOahtdAeaKqmOsavmsWsy6lZeuR7sD6MN8XD63HAjqlt1EtHUfLHcYo0TWgH0b7dnytzUFy7dWzbY72R4_ecQrVPgFxq9qJcT6Gy85R9MatxIeAb0Z_McRlVUY6mFVEmtM_--OoDAfNfASkikA1iw4Cgit1p0Xhhm8Y-Qqs9T9s3RQYlbU0Oj1ZI8ocJe97z9Bd37VM-l0bqfk9Iylqr7tBHsRhImTlfKvKbk9bMZ-C8IQZvEOxPb",
    feedThumb:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAeweoCuz_VO4J0wKgiBPLxxEsGIzPr1TE3ItVOfeIZvcIKmeQqmpT0vBj5OHcVd0WkQH17gkQodvGq0LswAXroHGVWoEKaKCqvTUaFxzLCIHJX_HcwhwUW04yPlzMbXK6OvdwrNkaHgT64z6b9xGgWEfaUEbOoPC0AjP1JBQsmfifYjOcaos6Bjs-v61fo1_q6eQVd1R4bA-R-yGoK85XMy5oYzserGrvzvqgdRQw7kOi-y8jTs5i9chSH4AgWJhvCHHDoXxPGnpa0",
  },
];

export const darkMapStyle = [
  { elementType: "geometry", stylers: [{ color: "#e8e8e8" }] },
  { elementType: "labels.text.fill", stylers: [{ color: "#555555" }] },
  { elementType: "labels.text.stroke", stylers: [{ color: "#f0f0f0" }] },
  // Hide all icons globally (removes green highway shields, POI icons, etc.)
  { elementType: "labels.icon", stylers: [{ visibility: "off" }] },
  {
    featureType: "administrative",
    elementType: "geometry.stroke",
    stylers: [{ color: "#bbbbbb" }],
  },
  {
    featureType: "administrative.land_parcel",
    elementType: "labels.text.fill",
    stylers: [{ color: "#777777" }],
  },
  { featureType: "poi", elementType: "labels.text.fill", stylers: [{ color: "#777777" }] },
  { featureType: "poi.park", elementType: "geometry", stylers: [{ color: "#d4e4d4" }] },
  { featureType: "poi.park", elementType: "labels.text.fill", stylers: [{ color: "#6a8a6a" }] },
  { featureType: "road", elementType: "geometry", stylers: [{ color: "#ffffff" }] },
  { featureType: "road", elementType: "labels.text.fill", stylers: [{ color: "#666666" }] },
  // Highway shields: gray background, no green
  { featureType: "road.highway", elementType: "geometry", stylers: [{ color: "#e0e0e0" }] },
  { featureType: "road.highway", elementType: "geometry.stroke", stylers: [{ color: "#cccccc" }] },
  { featureType: "road.highway", elementType: "labels.text.fill", stylers: [{ color: "#555555" }] },
  { featureType: "road.highway", elementType: "labels.text.stroke", stylers: [{ color: "#f0f0f0" }] },
  { featureType: "road.arterial", elementType: "labels.text.fill", stylers: [{ color: "#666666" }] },
  { featureType: "transit", elementType: "geometry", stylers: [{ color: "#e0e0e0" }] },
  { featureType: "transit", elementType: "labels.text.fill", stylers: [{ color: "#777777" }] },
  { featureType: "transit.station", elementType: "labels.icon", stylers: [{ visibility: "off" }] },
  { featureType: "water", elementType: "geometry", stylers: [{ color: "#c8d4e0" }] },
  { featureType: "water", elementType: "labels.text.fill", stylers: [{ color: "#8a9baa" }] },
];