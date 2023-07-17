const data = ({ $auth, redirect }) => {
    const { user_type } = $auth.user;

    switch (user_type) {
        case 'master':
            redirect('/master');
            break;
        case 'company':
            redirect('/dashboard');
            break;
        case 'employee':
            redirect('/employees/dashboard');
            break;
        case 'manager':
            redirect('/manager/dashboard');
            break;

        default:
            redirect('/dashboard');
            break;
    }
};

export default data;  