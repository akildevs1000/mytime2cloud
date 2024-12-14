export default (data, file_name = "download-result.csv") => {
  // Validate that data is an array of objects
  if (!Array.isArray(data) || data.length === 0 || typeof data[0] !== "object") {
    throw new Error("Invalid data: data should be a non-empty array of JSON objects.");
  }

  // Validate that file_name is a string
  if (typeof file_name !== "string") {
    throw new Error("Invalid file_name: file_name should be a string.");
  }

  // Create CSV header from object keys
  let header = Object.keys(data[0]).join(",") + "\n";

  // Create CSV rows from object values
  let rows = data.map((e) => Object.values(e).join(",").trim()).join("\n");

  // Create a download link and trigger the download
  let element = document.createElement("a");
  element.setAttribute(
    "href",
    "data:text/csv;charset=utf-8," + encodeURIComponent(header + rows)
  );
  element.setAttribute("download", file_name);
  document.body.appendChild(element);
  element.click();
  document.body.removeChild(element);
};
