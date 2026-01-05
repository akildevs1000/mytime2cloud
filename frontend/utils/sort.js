export function sortByKey(array, key) {
  return array.sort(function (a, b) {
    var A = (a[key] || "").toString().toUpperCase();
    var B = (b[key] || "").toString().toUpperCase();
    return A.localeCompare(B);
  });
}
