const data = ({ $auth, redirect }) => {
  const { user_type } = $auth.user;
  console.log("user_type", user_type);
  switch (user_type) {
    case "master":
      redirect("/master");
      break;

    case "branch":
      redirect("/dashboard2");
      break;
    case "employee":
      redirect("/employees/dashboard2");
      break;
    case "manager":
      redirect("/dashboard2");
      break;
    case "company":
      redirect("/dashboard2");
      break;
    default:
      redirect("/login");
      break;
  }
};

export default data;
