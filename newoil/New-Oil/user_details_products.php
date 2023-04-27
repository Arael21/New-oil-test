<?php

include 'config.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
};

if (isset($_POST['register'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
    $select_user->execute([$name, $email]);

    if ($select_user->rowCount() > 0) {
        $message[] = 'username or email already exists!';
    } else {
        if ($pass != $cpass) {
            $message[] = 'confirm password not matched!';
        } else {
            $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
            $insert_user->execute([$name, $email, $cpass]);
            $message[] = 'registered successfully, login now please!';
        }
    }
}

if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $qty = $_POST['qty'];
    $qty = filter_var($qty, FILTER_SANITIZE_STRING);
    $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
    $update_qty->execute([$qty, $cart_id]);
    $message[] = 'cart quantity updated!';
}

if (isset($_GET['delete_cart_item'])) {
    $delete_cart_id = $_GET['delete_cart_item'];
    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$delete_cart_id]);
    header('location:index.php');
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('location:index.php');
}

if (isset($_POST['add_to_cart'])) {

    if ($user_id == '') {
        $message[] = 'please login first!';
    } else {

        $pid = $_POST['pid'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $image = $_POST['image'];
        $qty = $_POST['qty'];
        $qty = filter_var($qty, FILTER_SANITIZE_STRING);

        $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
        $select_cart->execute([$user_id, $name]);

        if ($select_cart->rowCount() > 0) {
            $message[] = 'already added to cart';
        } else {
            $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
            $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
            $message[] = 'added to cart!';
        }
    }
}

if (isset($_POST['order'])) {

    if ($user_id == '') {
        $message[] = 'please login first!';
    } else {
        $name = $_POST['name'];
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        $number = $_POST['number'];
        $number = filter_var($number, FILTER_SANITIZE_STRING);
        $address = 'flat no.' . $_POST['flat'] . ', ' . $_POST['street'] . ' - ' . $_POST['pin_code'];
        $address = filter_var($address, FILTER_SANITIZE_STRING);
        $method = $_POST['method'];
        $method = filter_var($method, FILTER_SANITIZE_STRING);
        $total_price = $_POST['total_price'];
        $total_products = $_POST['total_products'];

        $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $select_cart->execute([$user_id]);

        if ($select_cart->rowCount() > 0) {
            $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
            $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
            $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
            $delete_cart->execute([$user_id]);
            $message[] = 'order placed successfully!';
        } else {
            $message[] = 'your cart empty!';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <title>New oil</title>
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            background-image: url('images/NVFTBACK.png');

            ;


        }
    </style>
</head>

<body>

    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '
         <div class="message">
            <span>' . $message . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
        }
    }
    ?>

    <!-- header section starts  -->

    <header class="header">

        <section class="flex">

            <a href="#home" class="logo"><span class="lgn">
                    <style>
                        .lgn {
                            color: rgb(37, 128, 37)
                        }
                    </style>N
                </span>ewoilcosmetic</a>

            <nav class="navbar">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="#menu">Menu</a>
                <a href="#order">Order</a>
                <a href="#faq">FAQ</a>
            </nav>

            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="user-btn" class="fas fa-user"></div>
                <div id="order-btn" class="fas fa-box"></div>
                <?php
                $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $count_cart_items->execute([$user_id]);
                $total_cart_items = $count_cart_items->rowCount();
                ?>
                <div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
            </div>

        </section>

    </header>

    <!-- header section ends -->

    <div class="user-account">

        <section>

            <div id="close-account"><span>close</span></div>

            <div class="user">
                <?php
                $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
                $select_user->execute([$user_id]);
                if ($select_user->rowCount() > 0) {
                    while ($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)) {
                        echo '<p>welcome ! <span>' . $fetch_user['name'] . '</span></p>';
                        echo '<a href="index.php?logout" class="btn">logout</a>';
                    }
                } else {
                    echo '<p><span>you are not logged in now!</span></p>';
                }
                ?>
            </div>

            <div class="display-orders">
                <?php
                $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $select_cart->execute([$user_id]);
                if ($select_cart->rowCount() > 0) {
                    while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                        echo '<p>' . $fetch_cart['name'] . ' <span>(' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')</span></p>';
                    }
                } else {
                    echo '<p><span>your cart is empty!</span></p>';
                }
                ?>
            </div>

            <div class="flex">

                <form action="user_login.php" method="post">
                    <h3>Login now</h3>
                    <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
                    <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20">
                    <input type="submit" value="login now" name="login" class="btn">
                </form>

                <form action="" method="post">
                    <h3>Register now</h3>
                    <input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required class="box" placeholder="enter your username" maxlength="20">
                    <input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
                    <input type="password" name="pass" required class="box" placeholder="enter your password" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
                    <input type="password" name="cpass" required class="box" placeholder="confirm your password" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
                    <input type="submit" value="register now" name="register" class="btn">
                </form>

            </div>

        </section>

    </div>

    <div class="my-orders">

        <section>

            <div id="close-orders"><span>close</span></div>

            <h3 class="title"> My orders </h3>

            <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
            $select_orders->execute([$user_id]);
            if ($select_orders->rowCount() > 0) {
                while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
            ?>
                    <div class="box">
                        <p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
                        <p> name : <span><?= $fetch_orders['name']; ?></span> </p>
                        <p> number : <span><?= $fetch_orders['number']; ?></span> </p>
                        <p> address : <span><?= $fetch_orders['address']; ?></span> </p>
                        <p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
                        <p> total_orders : <span><?= $fetch_orders['total_products']; ?></span> </p>
                        <p> total price : <span>$<?= $fetch_orders['total_price']; ?>/-</span> </p>
                        <p> payment status : <span style="color:<?php if ($fetch_orders['payment_status'] == 'pending') {
                                                                    echo 'red';
                                                                } else {
                                                                    echo 'green';
                                                                }; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty">nothing ordered yet!</p>';
            }
            ?>

        </section>

    </div>

    <div class="shopping-cart">

        <section>

            <div id="close-cart"><span>close</span></div>

            <?php
            $grand_total = 0;
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
                while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                    $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
                    $grand_total += $sub_total;
            ?>
                    <div class="box">
                        <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this cart item?');"></a>
                        <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
                        <div class="content">
                            <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
                            <form action="" method="post">
                                <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
                                <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
                                <button type="submit" class="fas fa-edit" name="update_qty"></button>
                            </form>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty"><span>your cart is empty!</span></p>';
            }
            ?>

            <div class="cart-total"> grand total : <span>$<?= $grand_total; ?>/-</span></div>

            <a href="#order" class="btn">order now</a>

        </section>

    </div>

    <div class="home-bg">

        <!-- home section starts  -->


        <!-- home section ends -->

        <!-- about section starts  -->

        <div class="child-article-container">
            <h1 class="header-article">Plus d'infos sur nos huiles</h1>
            <!-- <h3 class="sm-header-article"></h3> -->
        </div>
        <article class="m-a-container">
            <div class="article-container">
                <img src="../New-Oil/images/Capture d’écran 2023-04-.jpg" alt="New-oil-cosmetics" width="300px" height="350px" />
                <!-- change to real infos -->
                <p class="random-txt">
                    <!-- <h1>Huile de pépins </h1> -->
                    L'huile de pépins est une huile végétale obtenue à partir des graines de diverses plantes, telles que les raisins, les pommes, les poires, les cerises et les prunes. Elle est riche en acides gras essentiels, en vitamines et en antioxydants, ce qui en fait une huile très appréciée pour ses bienfaits pour la santé.
                    <br />
                    L'huile de pépins de raisin est peut-être la plus connue et la plus utilisée. Elle est souvent utilisée en cuisine pour sa saveur légèrement fruitée et sa haute résistance à la chaleur, ce qui en fait une huile de friture populaire. Elle est également utilisée en cosmétique pour ses propriétés hydratantes et anti-âge.
                    Il est important de noter que l'huile de pépins de raisin peut être sensible à l'oxydation, il est donc important de la stocker correctement dans un endroit frais et sec et de l'utiliser dans un délai raisonnable après l'ouverture. Il est également recommandé de choisir une huile de pépins de raisin pressée à froid pour conserver ses propriétés nutritionnelles et ses bienfaits pour la peau et les cheveux.

                    En conclusion, l'huile de pépins de raisin est une huile végétale polyvalente et bénéfique pour la santé. Elle peut être utilisée en cosmétique pour hydrater et nourrir la peau et les cheveux, et en cuisine pour ajouter de la saveur et des nutriments à vos plats préférés.
                    <!-- all buttons should have the add to cart function -->

                </p>
            </div>
        </article>
        <article class="m-a-container">
            <div class="article-container">
                <img src="../New-Oil/images/Capture d’écran 2.jpg" alt="New-oil-cosmetics" width="300px" height="350px" />
                <!-- change to real infos about products -->
                <p class="random-txt">
                    L'huile essentielle de myrte est extraite des feuilles et des fleurs de l'arbuste de myrte commun, également appelé myrte vert, qui pousse dans les régions méditerranéennes. Utilisée depuis l'Antiquité pour ses propriétés médicinales et aromatiques, cette huile essentielle est devenue populaire pour ses bienfaits pour la santé et la beauté.

                    Les bienfaits pour la santé de l'huile essentielle de myrte sont nombreux. Elle est considérée comme un expectorant naturel et est souvent utilisée pour soulager les symptômes de la toux, du rhume et de la bronchite. Elle est également utilisée pour soulager les douleurs musculaires et articulaires, ainsi que pour stimuler la circulation sanguine.

                    L'huile essentielle de myrte est également connue pour ses propriétés antiseptiques et antibactériennes, ce qui en fait un excellent choix pour les soins de la peau. Elle peut être utilisée pour traiter les infections cutanées, comme l'acné et les éruptions cutanées, ainsi que pour hydrater la peau sèche et irritée.

                    En aromathérapie, l'huile essentielle de myrte est utilisée pour soulager le stress, l'anxiété et la tension nerveuse. Elle est également considérée comme un aphrodisiaque naturel et peut être utilisée pour améliorer la libido.

                </p>
            </div>
        </article>
        <article class="m-a-container">
            <div class="article-container">
                <img src="../New-Oil/images/Capture d’écran .png" alt="New-oil-cosmetics" width="300px" height="350px" />
                <!-- change to real infos -->
                <p class="random-txt">
                    L'huile essentielle de lentisque, également connue sous le nom d'huile essentielle de pistachier lentisque, est extraite des feuilles et des rameaux de l'arbuste de lentisque, qui pousse principalement dans les régions méditerranéennes. Utilisée depuis l'Antiquité pour ses propriétés médicinales et cosmétiques, cette huile essentielle est devenue populaire pour ses nombreux bienfaits pour la santé et la beauté.

                    L'huile essentielle de lentisque est particulièrement connue pour ses propriétés anti-inflammatoires et analgésiques. Elle est souvent utilisée pour soulager les douleurs articulaires et musculaires, ainsi que les douleurs menstruelles. Elle peut également être utilisée pour traiter les infections des voies respiratoires, telles que la bronchite et la sinusite.

                    En aromathérapie, l'huile essentielle de lentisque est utilisée pour soulager le stress et l'anxiété. Elle peut aider à calmer l'esprit et à favoriser la relaxation. Elle est également considérée comme un tonique pour le système nerveux et peut aider à améliorer la concentration et la mémoire.

                    L'huile essentielle de lentisque est également bénéfique pour la peau. Elle est souvent utilisée pour traiter les problèmes de peau tels que l'acné, l'eczéma et le psoriasis. Elle peut également aider à réduire l'apparence des rides et des ridules, en laissant la peau lisse et rajeunie.

                </p>
            </div>
        </article>
        <article class="m-a-container">
            <div class="article-container">
                <img src="../New-Oil/images/Capture d’écran 2023-04-26 à.jpg" alt="New-oil-cosmetics" width="300px" height="350px" />
                <!-- change to real infos -->
                <p class="random-txt">
                    L'huile d'abricot est une huile végétale légère et douce extraite des noyaux d'abricots. Cette huile est riche en acides gras essentiels, en vitamine E et en antioxydants, ce qui en fait un excellent choix pour la peau et les cheveux.

                    En cosmétique, l'huile d'abricot est souvent utilisée pour ses propriétés hydratantes et nourrissantes. Elle peut aider à hydrater la peau sèche et à la protéger contre les dommages environnementaux. Elle est également bénéfique pour les peaux sensibles et irritées, car elle peut aider à apaiser l'inflammation et à réduire les rougeurs.

                    L'huile d'abricot est également bénéfique pour les cheveux. Elle peut aider à renforcer les mèches et à prévenir la casse, tout en leur donnant une apparence plus brillante et plus saine. Elle peut également être utilisée pour traiter les problèmes de cuir chevelu, tels que les démangeaisons et les pellicules.
                    Il est important de noter que l'huile d'abricot peut être sensible à l'oxydation, il est donc important de la stocker correctement dans un endroit frais et sec et de l'utiliser dans un délai raisonnable après l'ouverture. Il est également recommandé de choisir une huile d'abricot pressée à froid pour conserver ses propriétés nutritionnelles et ses bienfaits pour la peau et les cheveux.
                </p>
            </div>
        </article>
        <article class="m-a-container">
            <div class="article-container">
                <img src="../New-Oil/images/Capture d’éc.jpg" alt="New-oil-cosmetics" width="300px" height="350px" />
                <!-- change to real infos -->
                <p class="random-txt">
                    L'huile d'amande est une huile végétale extraite des amandes douces, riches en nutriments et en acides gras bénéfiques pour la santé. Elle est souvent utilisée en cuisine et en cosmétique pour ses nombreuses propriétés nourrissantes et hydratantes.

                    Sur le plan nutritionnel, l'huile d'amande est riche en acides gras mono-insaturés et polyinsaturés, ainsi qu'en vitamines E et K. Ces nutriments sont essentiels pour maintenir la santé du cœur, des os et de la peau. Elle contient également des antioxydants qui peuvent aider à prévenir les dommages causés par les radicaux libres dans le corps.

                    En cosmétique, l'huile d'amande est souvent utilisée comme huile de massage, car elle pénètre facilement dans la peau et laisse une sensation de douceur et d'hydratation. Elle peut également être utilisée comme huile pour le visage, car elle est légère et non grasse, ce qui en fait un excellent choix pour les peaux sensibles.

                    L'huile d'amande est également utilisée comme ingrédient dans les produits de soins capillaires, tels que les revitalisants et les masques capillaires. Elle peut aider à renforcer les cheveux et à prévenir la casse, tout en leur donnant une apparence plus brillante et plus saine.

                </p>
            </div>
        </article>
        <article class="m-a-container">
            <div class="article-container">
                <img src="../New-Oil//images/Capture .jpg" alt="New-oil-cosmetics" width="300px" height="350px" />
                <!-- change to real infos -->
                <p class="random-txt">
                    L'huile d'argan est une huile végétale riche en nutriments, extraite des graines de l'arganier, un arbre qui pousse exclusivement dans les régions semi-arides du Maroc. Utilisée depuis des siècles dans la cuisine et la médecine traditionnelle, l'huile d'argan est devenue populaire en cosmétique pour ses nombreux bienfaits pour la peau et les cheveux.

                    L'huile d'argan est riche en acides gras essentiels, en vitamine E, en antioxydants et en stérols, ce qui en fait un excellent choix pour hydrater, nourrir et protéger la peau et les cheveux. Elle peut être utilisée comme huile de massage, huile pour le visage et le corps, huile pour les cheveux, huile pour les ongles et même comme ingrédient dans les produits de soins pour bébés.

                    En cosmétique, l'huile d'argan est particulièrement appréciée pour ses propriétés hydratantes et anti-âge. Elle peut aider à réduire l'apparence des rides et des ridules, tout en laissant la peau douce et lisse. Elle est également bénéfique pour les peaux sèches et irritées, car elle peut aider à restaurer l'équilibre naturel de l'hydratation de la peau.

                    L'huile d'argan est également bénéfique pour les cheveux. Elle peut aider à renforcer les cheveux et à prévenir la casse, tout en leur donnant une apparence plus brillante et plus saine. Elle peut également être utilisée pour traiter les problèmes de cuir chevelu, tels que les démangeaisons et les pellicules.

                </p>
            </div>
        </article>
        <article class="m-a-container">
            <div class="article-container">
                <img src="../New-Oil/images/Capture .jpg" alt="New-oil-cosmetics" width="300px" height="350px" />
                <!-- change to real infos -->
                <p class="random-txt">
                    L'huile de ricin est une huile végétale extraite des graines de la plante de ricin. Cette huile est riche en acide ricinoléique, un acide gras unique qui lui confère de nombreuses propriétés bénéfiques pour la santé.

                    Sur le plan cosmétique, l'huile de ricin est souvent utilisée pour ses propriétés hydratantes et nourrissantes. Elle peut aider à hydrater la peau sèche et à la protéger contre les dommages environnementaux. Elle est également bénéfique pour les cheveux, car elle peut aider à renforcer les mèches et à réduire la casse, tout en leur donnant une apparence plus saine et plus brillante.

                    L'huile de ricin est également utilisée pour ses propriétés thérapeutiques. Elle peut aider à soulager la douleur et l'inflammation associées à l'arthrite et à d'autres affections. Elle peut également aider à soulager la constipation et à améliorer la santé digestive en agissant comme un laxatif doux.

                    En outre, l'huile de ricin peut aider à favoriser la croissance des cheveux et des ongles en stimulant la circulation sanguine dans la zone d'application. Elle peut également être utilisée pour traiter les problèmes de peau tels que l'acné, les cicatrices et les taches brunes en réduisant l'inflammation et en favorisant la guérison.

                    Il est important de noter que l'huile de ricin doit être utilisée avec précaution, car elle peut causer des effets secondaires indésirables tels que des nausées et des crampes abdominales si elle est ingérée en grande quantité. Elle doit également être évitée pendant la grossesse car elle peut stimuler les contractions utérines.



                </p>
            </div>
        </article>
        <article class="m-a-container">
            <div class="article-container">
                <img src="../New-Oil/images/produits/chauvre.png" alt="New-oil-cosmetics" width="300px" height="350px" />
                <!-- change to real infos -->
                <p class="random-txt">
                    L'huile de chanvre est une huile végétale extraite des graines de la plante de chanvre. Cette huile est de plus en plus populaire en raison de ses nombreux bienfaits pour la santé, notamment pour la peau, les cheveux et les fonctions corporelles.

                    Sur le plan nutritionnel, l'huile de chanvre est riche en acides gras essentiels, notamment en acide linoléique et en acide alpha-linolénique, qui sont importants pour maintenir la santé du cœur et pour le fonctionnement du système nerveux. Elle est également riche en vitamines et en minéraux, tels que la vitamine E, le fer et le zinc.

                    En cosmétique, l'huile de chanvre est souvent utilisée pour ses propriétés hydratantes et apaisantes. Elle peut aider à réduire l'inflammation et à apaiser les peaux sensibles ou irritées. Elle est également bénéfique pour les cheveux, car elle peut aider à renforcer les mèches et à réduire la casse, tout en leur donnant une apparence plus saine et plus brillante.

                    L'huile de chanvre est également utilisée pour ses propriétés thérapeutiques. Elle peut aider à réduire l'anxiété et le stress, ainsi que les symptômes associés à certaines affections, telles que l'arthrite et les troubles du sommeil. Elle est également utilisée pour ses propriétés anti-inflammatoires, qui peuvent aider à soulager la douleur associée à l'inflammation.
                </p>
            </div>
        </article>
        <!-- about section ends -->

        <!-- menu section starts  -->



        <!-- menu section ends -->


        <!-- order section starts  -->

        <section class="order" id="order">

            <!-- <h1 class="heading">Order now</h1> -->

            <form action="" method="post">

                <div class="display-orders">

                    <?php
                    $grand_total = 0;
                    $cart_item[] = '';
                    $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                    $select_cart->execute([$user_id]);
                    if ($select_cart->rowCount() > 0) {
                        while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                            $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
                            $grand_total += $sub_total;
                            $cart_item[] = $fetch_cart['name'] . ' ( ' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ' ) - ';
                            $total_products = implode($cart_item);
                            echo '<p>' . $fetch_cart['name'] . ' <span>(' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')</span></p>';
                        }
                    } else {
                        echo '<p class="empty"><span>your cart is empty!</span></p>';
                    }
                    ?>

                </div>
                <!-- 
      <div class="grand-total"> Grand total : <span>$<?= $grand_total; ?>/-</span></div>

      <input type="hidden" name="total_products" value="<?= $total_products; ?>">
      <input type="hidden" name="total_price" value="<?= $grand_total; ?>">

      <div class="flex">
         <div class="inputBox">
            <span> Name :</span>
            <input type="text" name="name" class="box" required placeholder="enter your name" maxlength="20">
         </div>
         <div class="inputBox">
            <span>Phone number :</span>
            <input type="number" name="number" class="box" required placeholder="enter your number" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;">
         </div>
         <div class="inputBox">
            <span>Payment method</span>
            <select name="method" class="box">
               <option value="cash on delivery">Cash on delivery</option>
               <option value="credit card">Credit card</option>
               <option value="paytm">paytm</option>
               <option value="paypal">Paypal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address line 01 :</span>
            <input type="text" name="flat" class="box" required placeholder="e.g. flat no." maxlength="50">
         </div>
         <div class="inputBox">
            <span>Address line 02 :</span>
            <input type="text" name="street" class="box" required placeholder="e.g. street name." maxlength="50">
         </div>
         <div class="inputBox">
            <span>Pin code :</span>
            <input type="number" name="pin_code" class="box" required placeholder="e.g. 123456" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;">
         </div>
      </div>

      <input type="submit" value="order now" class="btn" name="order">

   </form>

</section> -->

                <!-- order section ends -->
                <!-- gallery section starts  -->

                <!-- <section class="gallery" id="gallery">

   <div class="swiper gallery-slider">
      <div class="swiper-wrapper">
         <img src="images/huilelentisque.jpg" class="swiper-slide" alt="">
         <img src="images/huiledericin.jpg" class="swiper-slide" alt="">
         <img src="images/huiledechanvre.jpg" class="swiper-slide" alt="">
         <img src="images/huiledamande.jpg" class="swiper-slide" alt="">
         <img src="images/huiledabricot.jpg" class="swiper-slide" alt="">
         <img src="images//huiledargan.jpg" class="swiper-slide" alt="">
      </div>
      <div class="swiper-pagination"></div>
   </div>

</section> -->

                <!-- gallery section ends -->


                <!-- faq section starts  -->

                <!-- <section class="faq" id="faq">

   <h1 class="heading">FAQ</h1>

   <div class="accordion-container">

      <div class="accordion active">
         <div class="accordion-heading">
            <span>Comment ca marche ?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span >Ça prend combien une livraison ?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>Comment vous ...</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>Quel est le mode de paiement</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>


      <div class="accordion">
         <div class="accordion-heading">
            <span>Quel est le processus?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

   </div>

</section> -->

                <!-- faq section ends -->
                <!-- SERVISES  section STARS  -->

                <section class="services">

                    <div class="box-container">

                        <div class="box">
                            <!-- change icon -->
                            <img src="images/icon-1.png" alt="">

                            <h3>Non-tested on animals</h3>
                            <p> Animals are safe with Newoilcosmetics , we don't test our products on aniamls</p>
                        </div>

                        <div class="box">
                            <img src="images/icon-2.png" alt="">
                            <h3>From nature</h3>
                            <p>Natural oils have been used for centuries for their health and beauty benefits. These oils are extracted from various plants and have numerous therapeutic properties that make them a popular choice for skin care, hair care, and overall well-being.</p>
                        </div>

                        <!-- <div class="box">
         <img src="images/icon-3.png" alt="">
         <h3>Ça vient de notre nature </h3>
         <p>Lorem ipsum dolor sit amet, consectetum ipsum dolor sit amet, consecteturm ipsum dolor sit amet, consectetur
         m ipsum dolor sit amet, consecteturr adipisicing elit. Libero, sunt?</p>
      </div> -->



                    </div>

                </section>

                <!-- services section ends -->


                <!-- footer section starts  -->

                <section class="footer" style="background-color:#332711" ;>

                    <div class="box-container" style="background-color:url("images/NVFTBACK.png");>

                        <div class="box">
                            <a href="tel:078888888"><i class="fas fa-phone"></i> 078888888</a>

                            <a href="mailto:New.oil@gmail.com"><i class="fas fa-envelope"></i> New.oil@gmail.com</a>
                            <a href="#"><i class="fas fa-map-marker-alt"></i> Casablanca maroc</a>
                        </div>

                        <!-- <div class="box">
         <a href="#home">home</a>
         <a href="#about">about</a>
         <a href="#menu">menu</a>
         <a href="#order">order</a>
         <a href="#faq">faq</a>
         <a href="#reviews">reviews</a>
      </div> -->

                        <div class="box">
                            <a href="#">facebook <i class="fab fa-facebook-f"></i></a>
                            <a href="#">instagram <i class="fab fa-instagram"></i></a>

                        </div>

                    </div>





                    <div class="credit">
                        &copy; copyright @ <?= date('Y'); ?> by <span>New oil</span> | all rights reserved!
                    </div>

                </section>

                <!-- footer section ends -->

                <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

                <script src="js/script.js"></script>

</body>

</html>