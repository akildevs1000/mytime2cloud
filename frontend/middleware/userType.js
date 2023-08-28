const data = ({ $auth, redirect }) => {
    const { user_type } = $auth.user;

    switch (user_type) {
        case 'master':
            redirect('/master');
            break;
        case 'employee':
            redirect('/employees/dashboard');
            break;
        default:
            redirect('/dashboard');
            break;
    }
};

export default data;  