<?php

class UsersController
{

    private $model;
    private $presenter;

    public function __construct($model, $presenter)
    {
        $this->model = $model;
        $this->presenter = $presenter;
    }

    public function getLogin() // Obtener la vista de Login
    {
        $this->presenter->render("view/LoginView.mustache");
    }

    public function getRegister()   // Obtener la vista de Registro
    {
        $this->presenter->render("view/RegisterView.mustache");
    }

    public function postRegister()  // Procesar el registro
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $surname = $_POST['surname'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $_SESSION['error'] = "";

        $hash = md5($username . $email . date("Y-m-d"));

        if($this->model->register($username, $password, $email, $name, $surname, $hash)){
            $user = $this->model->getUserByUsername($username);
            $id = $user[0]['_id'];
            $link = "/Users/validateEmail?id=" . $id . "&hash=" . $hash;
            $this->presenter->render("view/RegisterSuccessView.mustache", ['link' => $link]);
        } else {
            $this->presenter->render("view/RegisterView.mustache", ['error' => $_SESSION['error']]);
        }
    }

    public function validateEmail()  // Validar el correo electrónico
    {
        $userId = $_GET['id'];
        $validationCode = $_GET['hash'];

        $user = $this->model->getUserById($userId);

        if ($user && $user[0]['hash'] == $validationCode) {
            // El código de validación coincide, marcar el correo electrónico como validado
            $this->model->setEmailValidated($userId);
            $_SESSION['message'] = "Correo electrónico validado correctamente";
            $this->presenter->render("view/LoginView.mustache", ['message' => $_SESSION['message']]);

        } else {
            echo "El código de validación no coincide";
        }
    }

    public function postLogin()  // Procesar el login
    {
        $usernameOrEmail = $_POST['username'];
        $password = $_POST['password'];
        $user = $this->model->login($usernameOrEmail, $password);

        if ($user) {
            $_SESSION['user'] = $user;
            $username = isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'][0]['username'] : null;
            $this->presenter->render("view/HomeView.mustache", ['username' => $username]);
        } else {
            $_SESSION['error'] = "Usuario o contraseña incorrectos";
            $this->presenter->render("view/LoginView.mustache", ['error' => $_SESSION['error']]);
        }
    }

    public function getProfile()  // Obtener la vista de Perfil
    {
        $username = isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'][0]['username'] : null;
        $user = $this->model->getUserByUsername($username);
        $this->presenter->render("view/ProfileView.mustache", ['user' => $user]);
    }

    public function logOut()  // Procesar el logout
    {
        session_destroy();
        header("Location: /Users/getLogin");
    }

}