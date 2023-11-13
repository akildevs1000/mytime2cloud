const data = async ({ $auth, redirect }) => {
  const { user_type, role } = $auth.user;
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

  if (user_type == "master") {
    redirect("/master");
    return;
  }

  redirect("/dashboard2");
};

export default data;
