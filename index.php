<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users management</title>
    <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <?php
    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $erreur = true;




    try {
        $db = new PDO("mysql:host = $servername;dbname=courssql", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Gestion des soumissions de formulaire pour la mise à jour ,  création  et suppression
        if (isset($_POST["confirm"])) {
            $erreur = update($db, $_POST["confirm"], $_POST["newName"], $_POST["newFname"], $_POST["newEmail"], $_POST["newPostcode"]);
        }
        if (isset($_POST["create"])) {
            $erreur = create($db, $_POST['name'], $_POST['fname'], $_POST['email'], $_POST['postcode']);
        }
        delete($db);
        // Récupération de données depuis la base de données
        $db->beginTransaction();

        $requeteSQL = $db->prepare("SELECT id, Nom, Prénom, Email, CodePostal FROM courssql.users");

        $requeteSQL->execute();
        $tableauRequete = $requeteSQL->fetchAll();
        $db->commit();

        $db = null;
    } catch (PDOException $e) {

        echo "Erreur : " . $e->getMessage();

        $db->rollback();
    }

    ?>
    <!-- On écrit notre table HTML et ausi ajouter un utilisateur-->
    <table>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Code Postal</th>
            <td><b>Action</b></td>
        </tr>
        <?php for ($i = 0; $i < count($tableauRequete); $i++) { ?>
            <tr>
                <form method="POST">
                    <?php if (isset($_POST['update'])) {

                        if ($_POST['update'] ==  $tableauRequete[$i]["id"]) { ?>
                            <td>
                                <label for="name"></label>
                                <input type="text" name="newName" value="<?= $tableauRequete[$i]["Nom"]; ?>">
                            </td>
                            <td>
                                <label for="fname"></label>
                                <input type="text" name="newFname" value="<?= $tableauRequete[$i]["Prénom"]; ?>">
                            </td>
                            <td>
                                <label for="email"></label>
                                <input type="text" name="newEmail" value="<?= $tableauRequete[$i]["Email"]; ?>">
                            </td>
                            <td>
                                <label for="codePostal"></label>
                                <input type="text" name="newPostcode" value="<?= $tableauRequete[$i]["CodePostal"]; ?>">
                            </td>
                            <td>
                                <button type=submit name="confirm" value=<?= $tableauRequete[$i]["id"] ?>> Valider </button>

                            <?php } else { ?>
                            <td><?php echo $tableauRequete[$i]["Nom"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["Prénom"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["Email"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["CodePostal"] ?></td>
                            <td><button type=submit name="update" value=<?php echo $tableauRequete[$i]["id"]; ?>> Modifier </button>
                            <?php }
                    } else {

                            ?>
                            <td><?php echo $tableauRequete[$i]["Nom"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["Prénom"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["Email"]; ?></td>
                            <td><?php echo $tableauRequete[$i]["CodePostal"] ?></td>
                            <td><button type=submit name="update" value=<?php echo $tableauRequete[$i]["id"]; ?>> Modifier </button>
                            <?php } ?>
                            </td>
                            <td>
                                <button class="delete-btn" type=submit name="delete" value=<?php echo $tableauRequete[$i]["id"]; ?>>Supprimer </button>
                            </td>
                </form>
            </tr>

        <?php } ?>

        <form method="post">
        <?php if (isset($_POST['add'])) { ?>
            <tr>
                <td>
                    <label for="name"></label>
                    <input type="text" placeholder="Nom" name="name">
                </td>

                <td>
                    <label for="fname"></label>
                    <input type="text" placeholder="Prénom" name="fname">
                </td>
                <td>
                    <label for="email"></label>
                    <input type="text" placeholder="Email" name="email">
                </td>
                <td>
                    <label for="CodePostal"></label>
                    <input type="text" placeholder="Code Postal" name="postcode">

                </td>
                <td>
                    <button type="submit" name="create">Valider</button>
                </td>
            </tr>
        <?php } ?>
        <td>
            <button type=submit name="add">Ajouter utilisateur</button>
        </td>
        </form>
    </table>
    <?php



    // fonction pour mise à jour qui prend en compte la fonction check lors d'un update;
    function update($db, $newId, $newName, $newFname, $newEmail, $newPostcode)
    {
        if (isset($_POST['confirm'])) {
            $requeteSQL = $db->prepare("UPDATE users SET id = '$newId', Nom=  '$newName', Prénom='$newFname', Email='$newEmail', CodePostal='$newPostcode' WHERE id = :id");
            $regex = check($newName, $newFname, $newEmail, $newPostcode);
            if ($regex === true) {
                $requeteSQL->execute([
                    ":id" => $_POST['confirm']
                ]);
            }
        }
        return $regex;
    }


    // fonction de suppression

    function delete($db)
    {
        if (isset($_POST['delete'])) {
            $requeteSQL = $db->prepare("DELETE FROM users WHERE id = :id");
            $requeteSQL->execute([":id" => $_POST['delete']]);
        }
    }





    function create($db, $name, $fname, $email, $postCode)
    {
        if (isset($_POST['create'])) {
            $requeteSQL = $db->prepare("INSERT INTO users ( Nom, Prénom, Email, CodePostal)VALUES('$name', '$fname', '$email', '$postCode')");
            $regex = check($name, $fname, $email, $postCode);
            if ($regex === true) {
                $requeteSQL->execute();
            }
        }
        return $regex;
    }


    // fonction pour vérifier les caractères dans les champs saisis et retourne une erreur en cas de non-match
    if ($erreur !== true) {
        echo "<p style='color: red;'>$erreur</p>";
    }

    function check($name, $fname, $email, $postCode)
    {
        if (!preg_match('/^[A-Za-z]+$/', $name)) {
            return "Veuillez saisir un nom valide";
        }

        if (!preg_match('/^[A-Za-z]+$/', $fname)) {
            return "Veuillez saisir un prénom valide";
        }

        if (!preg_match('/^[A-zÀ-ÿ0-9_.-]*@[a-z]*\.[a-z]{2,5}$/i', $email)) {
            return "Veuillez saisir un email valide";
        }
        
        if (!preg_match("/^[0-9]{5}$/", $postCode)) {
            return "Veuillez saisir un code postal valide";
        }
        return true;
    }

    ?>
</body>

</html>