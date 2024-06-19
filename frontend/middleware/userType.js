const data = async ({ $auth, redirect }) => {
  const { user_type, role } = $auth.user;

  if (user_type.branch_id == 0 && user_type.is_master == false) {
    return redirect("/login");
  }
  if (user_type == "department") {
    return redirect("/dashboard");
  }
  if (user_type == "master") {
    return redirect("/master");
  }

  let roleType = "";
  if (role && role.role_type) {
    roleType = role.role_type.toLowerCase();
  }

  if (/guard/.test(roleType)) {
    return redirect("/guard/guard-dashboard");
    return;
  }
  if (/host/.test(roleType)) {
    return redirect("/host/visitor-dashboard");
  }

  if ($auth.user.role_id == 0 && user_type == "employee") {
    try {
      window.location.href = process.env.EMPLOYEE_APP_URL;
      return "";
    } catch (e) {
      return redirect("logout");
    }
  }
  if (user_type.branch_id == 0 && user_type.is_master == false) {
    return redirect("logout");
  } else {
    return redirect("/dashboard");
  }

};

export default data;
