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

        default:
            redirect('/');
            break;
    }
};

export default data;  