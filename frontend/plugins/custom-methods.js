export default ({ app }, inject) => {
  inject("dateFormat", {
    format1: (inputdate) => {
      // Create a Date object with the date "2023-09-13"  Output Sun, Jan 01, 2023
      const inputDate = new Date(inputdate);
      const options = {
        year: "numeric",
        month: "short",
        day: "2-digit",
        weekday: "short",
      };
      const formattedDate = inputDate.toLocaleDateString("en-US", options);
      return formattedDate;
    },
    format2: (inputdate) => {
      // Create a Date object with the date "2023-09-13"  Output: "23-09-13"
      const date = new Date(inputdate);

      const year = date.getFullYear().toString().slice(-2);
      const month = (date.getMonth() + 1).toString().padStart(2, "0"); // Note: Month is zero-indexed
      const day = date.getDate().toString().padStart(2, "0");

      return `${year}-${month}-${day}`;
    },

    can(per, thisobj) {
      let u = thisobj.$auth.user;

      return (
        (u && u.permissions.some((e) => e == per || per == "/")) ||
        u.is_master ||
        u.user_type == "branch"
      );
    },
  });
};
