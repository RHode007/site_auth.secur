$( document ).ready(function() {
    $('button[type="submit"]').click(function(){

        /*Валидация полей формы*/
        $('#regForm').validate({
            //Правила валидации
            rules: {
                fname: {
                    required: true,
                },
                email: {
                    required: true,
                    email: true
                },
                password1: {
                    required: true,
                },

            },
            //Сообщения об ошибках
            messages: {
                fname: {
                    required: "Обязательно укажите имя",
                },
                email: {
                    required: "Обязательно укажите Email",
                },
                password1: {
                    required: "Обязательно укажите пароль",
                },
            },

            /*Отправка формы в случае успеха валидации*/
            submitHandler: function(){
                sendAjaxForm('regForm', './controller/c_users.php'); //Вызываем функцию отправки формы
                return false;
            }
        });
    });

    function sendAjaxForm(feedback, url) {
        var fname = document.getElementById('rFname');
        var photo = document.getElementById('rPhoto');
        var email = document.getElementById('rEmail');
        var rPass1 = document.getElementById('rPassword1');
        var rPass2 = document.getElementById('rPassword2');
        var rPval1 = document.getElementById('ePstate1')
        var rPval2 = document.getElementById('ePstate2')
        var rCval = document.getElementById('rCaptVal')
        var rEval = document.getElementById('eEstate')
        $.ajax({
            url:     url, //url страницы (ajax-form.php)
            type:     "POST", //метод отправки
            data: $("#"+feedback).serialize(),  // Сеарилизуем объекты формы
            success: function(response) { //Данные отправлены успешно
                console.log(response);
                data = JSON.parse(response);
                if (data['errEmail']) {
                    rEval.innerHTML = (data['errEmail']);
                    email.classList.add('is-invalid');
                    grecaptcha.reset();
                } else {
                    email.classList.add('is-valid');
                }
                if (data['errPass']) {
                    rPass1.classList.add('is-invalid');
                    rPass2.classList.add('is-invalid');
                    rPval1.innerHTML = data['errPass'];
                    rPval2.innerHTML = data['errPass'];
                    grecaptcha.reset();
                } else {
                    rPass1.classList.remove('is-invalid');
                    rPass2.classList.remove('is-invalid');
                    rPass1.classList.add('is-valid');
                    rPass2.classList.add('is-valid');
                    rPval1.innerHTML = "";
                    rPval2.innerHTML = "";
                    grecaptcha.reset();
                }
                if (data['errCaptcha'] != null) {
                    rCval.innerHTML = data['errCaptcha'];
                    grecaptcha.reset();
                }
                if (data['extMail']) {
                    rEval.innerHTML = (data['extMail']);
                    email.classList.add('is-invalid');
                    grecaptcha.reset();
                } else {
                    email.classList.add('is-valid');
                }
                if (data['message'] == 'success') {
                    document.getElementById('status').innerHTML = "<h3 class='text-center text-primary'>Registration Complete...!</h3>"
                    setTimeout(() => {

                        window.location.href = 'index.php';
                    }, 3000);
                }
                if (data['message'] == 'error') {
                    document.getElementById('Estatus').innerHTML = "Something went wrong...!";
                    setTimeout(() => {
                        location.reload();
                    }, 3000);

                }
            },
            error: function(response) { // Данные не отправлены

                //Ваш код если ошибка
                alert('Ошибка отправления');
            }
        });

    }
});