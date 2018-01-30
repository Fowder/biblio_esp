<?php
/**
 * La configuration de base de votre installation WordPress.
 *
 * Ce fichier contient les réglages de configuration suivants : réglages MySQL,
 * préfixe de table, clés secrètes, langue utilisée, et ABSPATH.
 * Vous pouvez en savoir plus à leur sujet en allant sur
 * {@link http://codex.wordpress.org/fr:Modifier_wp-config.php Modifier
 * wp-config.php}. C’est votre hébergeur qui doit vous donner vos
 * codes MySQL.
 *
 * Ce fichier est utilisé par le script de création de wp-config.php pendant
 * le processus d’installation. Vous n’avez pas à utiliser le site web, vous
 * pouvez simplement renommer ce fichier en "wp-config.php" et remplir les
 * valeurs.
 *
 * @package WordPress
 */

// ** Réglages MySQL - Votre hébergeur doit vous fournir ces informations. ** //
/** Nom de la base de données de WordPress. */
define('DB_NAME', 'biblio_esp');

/** Utilisateur de la base de données MySQL. */
define('DB_USER', 'root');

/** Mot de passe de la base de données MySQL. */
define('DB_PASSWORD', 'simplonco');

/** Adresse de l’hébergement MySQL. */
define('DB_HOST', 'localhost');

/** Jeu de caractères à utiliser par la base de données lors de la création des tables. */
define('DB_CHARSET', 'utf8mb4');

/** Type de collation de la base de données.
  * N’y touchez que si vous savez ce que vous faites.
  */
define('DB_COLLATE', '');

/**#@+
 * Clés uniques d’authentification et salage.
 *
 * Remplacez les valeurs par défaut par des phrases uniques !
 * Vous pouvez générer des phrases aléatoires en utilisant
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ le service de clefs secrètes de WordPress.org}.
 * Vous pouvez modifier ces phrases à n’importe quel moment, afin d’invalider tous les cookies existants.
 * Cela forcera également tous les utilisateurs à se reconnecter.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'is4an#v}WKw4-~-+iZq2=aiO-bC5m(n3igkFlVfS(~wy|-@,}U=lG~=1KgZ1vvq;');
define('SECURE_AUTH_KEY',  'to7H,tf[z^l{pi:k+K]xHV7jA,088.:KhR^k$V|EsIo+0y&W`D}F!x9LSU!.$/iI');
define('LOGGED_IN_KEY',    '3I=eJ uxW&/vf-:Aivh[T8`Cb-Rar<;DR5uDniwfp_}r>_DQ(3](uB=L}#@9kIU5');
define('NONCE_KEY',        ';PF*`JHNpoe z:i dp%xfw kHDCGP?l#A#RG-,}&z3K:0Mo~hSH2B~a9Y4g*T5VE');
define('AUTH_SALT',        '.^-T}[>ICsQI6EQp:VEJ=ZEVI?S]*V.<i$44r]}m5F4Z#7v}s&dki_{$5b|61:K4');
define('SECURE_AUTH_SALT', '$ic-IWyOQgx;0K^lCh.L.GL{tD9NL,I_Aksq[:.43Kip/IL16:H glargbK0s;7u');
define('LOGGED_IN_SALT',   'j*U3J] AJK/`pfhpt.-&T!;872Q9Ks:JFbEc?!=0,:M.H>,7<FM3<,4Cj.o0UPMO');
define('NONCE_SALT',       'NCohh_[B{Cb5>)xaTa4ko}K~=vOxWn^?0ND-JEtw4!!;.!~nkE< tWd4YN Poo9Y');
/**#@-*/

/**
 * Préfixe de base de données pour les tables de WordPress.
 *
 * Vous pouvez installer plusieurs WordPress sur une seule base de données
 * si vous leur donnez chacune un préfixe unique.
 * N’utilisez que des chiffres, des lettres non-accentuées, et des caractères soulignés !
 */
$table_prefix  = 'wp_biblio';

/**
 * Pour les développeurs : le mode déboguage de WordPress.
 *
 * En passant la valeur suivante à "true", vous activez l’affichage des
 * notifications d’erreurs pendant vos essais.
 * Il est fortemment recommandé que les développeurs d’extensions et
 * de thèmes se servent de WP_DEBUG dans leur environnement de
 * développement.
 *
 * Pour plus d’information sur les autres constantes qui peuvent être utilisées
 * pour le déboguage, rendez-vous sur le Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* C’est tout, ne touchez pas à ce qui suit ! */

/** Chemin absolu vers le dossier de WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Réglage des variables de WordPress et de ses fichiers inclus. */
require_once(ABSPATH . 'wp-settings.php');
define('FS_METHOD', 'direct');
