const data = async ({ $auth, redirect }) => {
  const { user_type, role } = $auth.user;

  if (user_type == "master") {
    redirect("/master");
    return;
  }

  let roleType = "";
  if (role && role.role_type) {
    roleType = role.role_type.toLowerCase();
  }

  if (/guard/.test(roleType)) {
    redirect("/guard/visitor-dashboard");
    return;
  }
  if (/host/.test(roleType)) {
    redirect("/host/visitor-dashboard");
    return;
  }

  if ($auth.user.role_id == 0 && user_type == "employee") {
    try {
      window.location.href = process.env.EMPLOYEE_APP_URL;
      return "";
    } catch (e) {
      redirect("login");
    }
  }

  redirect("/dashboard2");
};

export default data;
