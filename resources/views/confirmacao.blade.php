<!DOCTYPE html>
<html>
<link rel="icon" type="imagem/png" href={{asset("img/cortaíicone3.png")}} />

<head>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Open+Sans');

        body {
            margin: 0px
        }

        .container {
            width: 100vw;
            height: 100vh;
            background: #f45d27;
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items: center
        }

        .box {
            padding-top: 5%;
            width: 60vw;
            height: 60vh;
            background: #fff;
            border-radius: 25px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            transition: 0.3s;

        }

        .box:hover {
            box-shadow: 0 8px 16px 0 rgba(0, 0, 0, 0.4);
        }

        .icone {
            align-items: right;
        }

        .desativa {
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -o-user-select: none;
            user-select: none;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="box desativa" style="text-align:center">
        <div class="icone desativa" unselectable="on">
            <img src={{asset("img/cortaíicone3.png")}} width=100 height=100>
        </div>
        <h2>Seu email foi confirmado!</h2>
        Nós da equipe Cortaí ficamos muito felizes em ter você como um dos nossos parceiros.
        <p>Você já pode acessar a sua conta pelo aplicativo, utilizando o email cadastrado e sua
            senha.</p>

    </div>
</div>

</body>

</html>
