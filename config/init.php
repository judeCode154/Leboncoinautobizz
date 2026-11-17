<?php
if (isset($conn)) {
    mysqli_query(
        $conn,
        "CREATE TABLE IF NOT EXISTS annonce (
            id_ads INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            description TEXT NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            category VARCHAR(100) DEFAULT NULL,
            id_users INT NOT NULL,
            views INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX (id_users),
            CONSTRAINT annonce_ibfk_1 FOREIGN KEY (id_users) REFERENCES users(id_users) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    mysqli_query(
        $conn,
        "CREATE TABLE IF NOT EXISTS favorites (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_users INT NOT NULL,
            id_ads INT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY id_users (id_users, id_ads),
            INDEX (id_ads),
            CONSTRAINT favorites_ibfk_1 FOREIGN KEY (id_users) REFERENCES users(id_users) ON DELETE CASCADE,
            CONSTRAINT favorites_ibfk_2 FOREIGN KEY (id_ads) REFERENCES annonce(id_ads) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    mysqli_query(
        $conn,
        "CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_expedi INT NOT NULL,
            id_desti INT NOT NULL,
            id_ads INT,
            content TEXT NOT NULL,
            lu TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX (id_expedi),
            INDEX (id_desti),
            INDEX (id_ads),
            CONSTRAINT messages_ibfk_1 FOREIGN KEY (id_expedi) REFERENCES users(id_users) ON DELETE CASCADE,
            CONSTRAINT messages_ibfk_2 FOREIGN KEY (id_desti) REFERENCES users(id_users) ON DELETE CASCADE,
            CONSTRAINT messages_ibfk_3 FOREIGN KEY (id_ads) REFERENCES annonce(id_ads) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    // Vérifier et ajouter la colonne 'id' si elle n'existe pas
    $resultatColonneId = mysqli_query(
        $conn,
        "SELECT COLUMN_NAME
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'messages'
         AND COLUMN_NAME = 'id'"
    );

  

    // Vérifier et ajouter la colonne 'lu' si elle n'existe pas
    $resultatColonne = mysqli_query(
        $conn,
        "SELECT COLUMN_NAME
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'messages'
         AND COLUMN_NAME = 'lu'"
    );

    if ($resultatColonne && mysqli_num_rows($resultatColonne) === 0) {
        mysqli_query($conn, "ALTER TABLE messages ADD COLUMN lu TINYINT(1) NOT NULL DEFAULT 0");
    }
}
?>

