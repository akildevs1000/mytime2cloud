const data = ({ $auth, redirect }) => {
  const { user_type } = $auth.user;
  console.log("user_type", user_type);
  switch (user_type) {
    case "master":
      redirect("/master");
      break;
    case "employee":
      redirect("/dashboard/employee");
      break;
    case "manager":
      redirect("/dashboard2");
      break;
    case "branch":
      redirect("/dashboard2");
      break;
    default:
      redirect("/dashboard/employee");
      break;
  }
};

export default data;
