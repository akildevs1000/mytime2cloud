/**
 * Format date to YYYY-MM-DD format
 * @param {Date|string} date - Date to format
 * @returns {string} - Formatted date
 */
export const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${d.getFullYear()}-${month}-${day}`;
};

/**
 * Get first and last day of current month
 * @returns {Object} - { fromDate, toDate }
 */
export const getCurrentMonthRange = () => {
  const now = new Date();
  const year = now.getFullYear();
  const month = now.getMonth() + 1;
  const lastDay = new Date(year, month, 0).getDate();

  const fromDate = `${year}-${String(month).padStart(2, '0')}-01`;
  const toDate = `${year}-${String(month).padStart(2, '0')}-${lastDay}`;

  return { fromDate, toDate };
};

/**
 * Build query string from object
 * @param {Object} params - Parameters object
 * @returns {string} - Query string
 */
export const buildQueryString = (baseUrl, params) => {
  let qs = baseUrl;
  const queryParams = new URLSearchParams();

  Object.keys(params).forEach(key => {
    const value = params[key];
    if (value !== null && value !== undefined && value !== '') {
      if (Array.isArray(value)) {
        queryParams.append(key, value.join(','));
      } else {
        queryParams.append(key, value);
      }
    }
  });

  const queryString = queryParams.toString();
  return queryString ? `${qs}?${queryString}` : qs;
};

/**
 * Validate email
 * @param {string} email - Email to validate
 * @returns {boolean}
 */
export const validateEmail = (email) => {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
};

/**
 * Validate date range
 * @param {string} fromDate - Start date
 * @param {string} toDate - End date
 * @returns {boolean}
 */
export const isValidDateRange = (fromDate, toDate) => {
  if (!fromDate || !toDate) return false;
  try {
    const from = new Date(fromDate);
    const to = new Date(toDate);
    return from <= to;
  } catch {
    return false;
  }
};

/**
 * Parse CSV response
 * @param {string} csvText - CSV text
 * @returns {Array} - Parsed rows
 */
export const parseCSV = (csvText) => {
  const rows = csvText.split('\n').filter(row => row.trim());
  return rows.map(row =>
    row.split(',').map(cell => cell.trim())
  );
};

/**
 * Generate CSV from array of objects
 * @param {Array} data - Data array
 * @param {Array} headers - Header keys
 * @returns {string} - CSV string
 */
export const generateCSV = (data, headers) => {
  const csvHeaders = headers.join(',');
  const csvRows = data.map(row =>
    headers.map(header => {
      const value = row[header];
      return typeof value === 'string' && value.includes(',')
        ? `"${value}"`
        : value;
    }).join(',')
  );
  return [csvHeaders, ...csvRows].join('\n');
};

/**
 * Download file
 * @param {Blob|string} content - File content
 * @param {string} filename - File name
 */
export const downloadFile = (content, filename) => {
  const blob = content instanceof Blob ? content : new Blob([content]);
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', filename);
  document.body.appendChild(link);
  link.click();
  link.parentNode.removeChild(link);
  window.URL.revokeObjectURL(url);
};

/**
 * Format time to HH:MM format
 * @param {string|Date} time - Time to format
 * @returns {string}
 */
export const formatTime = (time) => {
  if (!time) return '';
  if (typeof time === 'string' && time.includes(':')) return time;
  const date = new Date(time);
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${hours}:${minutes}`;
};

/**
 * Calculate date difference in days
 * @param {string|Date} date1
 * @param {string|Date} date2
 * @returns {number}
 */
export const dateDifferenceInDays = (date1, date2) => {
  const d1 = new Date(date1);
  const d2 = new Date(date2);
  const diffTime = Math.abs(d2 - d1);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

/**
 * Truncate string
 * @param {string} str - String to truncate
 * @param {number} length - Max length
 * @returns {string}
 */
export const truncateString = (str, length = 20) => {
  return str && str.length > length ? str.substring(0, length) + '...' : str;
};

/**
 * Format status badge
 * @param {string} status - Status value
 * @returns {Object} - { className, label }
 */
export const formatStatusBadge = (status) => {
  const statusMap = {
    present: { className: 'badge-success', label: 'Present' },
    absent: { className: 'badge-danger', label: 'Absent' },
    late: { className: 'badge-warning', label: 'Late' },
    pending: { className: 'badge-info', label: 'Pending' },
    completed: { className: 'badge-success', label: 'Completed' },
    missing: { className: 'badge-danger', label: 'Missing' },
  };

  return statusMap[status?.toLowerCase()] || { className: 'badge-secondary', label: status };
};

/**
 * Merge multiple arrays and remove duplicates
 * @param  {...Array} arrays - Arrays to merge
 * @returns {Array}
 */
export const mergeUnique = (...arrays) => {
  return [...new Set(arrays.flat())];
};

/**
 * Group array by key
 * @param {Array} array - Array to group
 * @param {string} key - Key to group by
 * @returns {Object}
 */
export const groupBy = (array, key) => {
  return array.reduce((result, item) => {
    const group = item[key];
    if (!result[group]) {
      result[group] = [];
    }
    result[group].push(item);
    return result;
  }, {});
};

/**
 * Sort array of objects
 * @param {Array} array - Array to sort
 * @param {string} key - Key to sort by
 * @param {string} order - 'asc' or 'desc'
 * @returns {Array}
 */
export const sortBy = (array, key, order = 'asc') => {
  return [...array].sort((a, b) => {
    if (a[key] < b[key]) return order === 'asc' ? -1 : 1;
    if (a[key] > b[key]) return order === 'asc' ? 1 : -1;
    return 0;
  });
};

/**
 * Filter array of objects
 * @param {Array} array - Array to filter
 * @param {Object} filters - Filter criteria
 * @returns {Array}
 */
export const filterBy = (array, filters) => {
  return array.filter(item =>
    Object.keys(filters).every(key => {
      const filterValue = filters[key];
      const itemValue = item[key];
      
      if (Array.isArray(filterValue)) {
        return filterValue.includes(itemValue);
      }
      if (filterValue === null || filterValue === undefined) {
        return true;
      }
      return itemValue === filterValue;
    })
  );
};

/**
 * Deep clone object
 * @param {Object} obj - Object to clone
 * @returns {Object}
 */
export const deepClone = (obj) => {
  if (obj === null || typeof obj !== 'object') return obj;
  if (obj instanceof Date) return new Date(obj.getTime());
  if (obj instanceof Array) return obj.map(item => deepClone(item));
  if (obj instanceof Object) {
    const clonedObj = {};
    for (const key in obj) {
      if (obj.hasOwnProperty(key)) {
        clonedObj[key] = deepClone(obj[key]);
      }
    }
    return clonedObj;
  }
};

/**
 * Check if object is empty
 * @param {Object} obj - Object to check
 * @returns {boolean}
 */
export const isEmpty = (obj) => {
  return obj && Object.keys(obj).length === 0;
};

/**
 * Convert object to array of key-value pairs
 * @param {Object} obj - Object to convert
 * @returns {Array}
 */
export const objectToArray = (obj) => {
  return Object.entries(obj).map(([key, value]) => ({ key, value }));
};

/**
 * Capitalize first letter
 * @param {string} str - String to capitalize
 * @returns {string}
 */
export const capitalize = (str) => {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
};
