-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-12-2023 a las 14:04:54
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `segundamoda`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `registro` (IN `username` VARCHAR(255), IN `pass` VARCHAR(255), IN `name` VARCHAR(255), IN `tel` INT(9), IN `email` VARCHAR(255))   BEGIN
    INSERT INTO Usuario (Usuario, Passwrd, Nombre, Telefono, CorreoElectronico, Rol)
    VALUES (username, pass, name, tel, email, 0);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerificarContraseña` (IN `p_Usuario` VARCHAR(255), IN `p_Contraseña` VARCHAR(255), OUT `p_Resultado` INT)   BEGIN
    DECLARE v_Password varchar(255);
    
    SELECT Passwrd INTO v_Password FROM Usuario WHERE Usuario = p_Usuario;
    
    IF v_Password IS NOT NULL AND v_Password = p_Contraseña THEN
        SET p_Resultado = 1; -- La contraseña coincide
    ELSE
        SET p_Resultado = 0; -- La contraseña no coincide
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncio`
--

CREATE TABLE `anuncio` (
  `IdAnuncio` int(100) NOT NULL,
  `Categoria` int(100) DEFAULT NULL,
  `Nombre` varchar(255) DEFAULT NULL,
  `Precio` double(8,2) DEFAULT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `IdUser` varchar(255) DEFAULT NULL,
  `Ubicacion` varchar(255) DEFAULT NULL,
  `EstadoDelAnuncio` int(100) DEFAULT NULL,
  `Visibilidad` int(100) DEFAULT NULL,
  `SYS_INSERTED` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `IdCategoria` int(100) NOT NULL,
  `Nombre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`IdCategoria`, `Nombre`) VALUES
(1, 'Camiseta'),
(2, 'Pantalon'),
(3, 'Zapatillas/Zapatos'),
(4, 'Accesorios'),
(5, 'Chaqueta/Sudadera'),
(6, 'Ropa interior');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadomaterial`
--

CREATE TABLE `estadomaterial` (
  `IdEstado` int(100) NOT NULL,
  `Estado` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estadomaterial`
--

INSERT INTO `estadomaterial` (`IdEstado`, `Estado`) VALUES
(1, 'Sin estrenar'),
(2, 'Como nuevo'),
(3, 'Poco usado'),
(4, 'Muy usado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foto`
--

CREATE TABLE `foto` (
  `IdFoto` int(100) NOT NULL,
  `IdAnuncio` int(100) DEFAULT NULL,
  `NumeroFoto` int(100) DEFAULT NULL,
  `Fichero` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial`
--

CREATE TABLE `historial` (
  `IdHistorial` int(2) NOT NULL,
  `Accion` varchar(3) NOT NULL,
  `IdAnuncio` int(100) NOT NULL,
  `Fecha` datetime NOT NULL,
  `IdUser` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peticion`
--

CREATE TABLE `peticion` (
  `IdPeticion` int(100) NOT NULL,
  `IdAnuncio` int(100) DEFAULT NULL,
  `IdUsuario` varchar(255) DEFAULT NULL,
  `EstadoSolicitud` int(100) DEFAULT NULL,
  `SYS_INSERTED` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `Usuario` varchar(255) NOT NULL,
  `Passwrd` varchar(255) DEFAULT NULL,
  `Nombre` varchar(255) DEFAULT NULL,
  `Telefono` int(9) DEFAULT NULL,
  `CorreoElectronico` varchar(255) DEFAULT NULL,
  `Rol` int(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vistaanuncioconfoto`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vistaanuncioconfoto` (
`IdAnuncio` int(100)
,`Categoria` int(100)
,`Nombre` varchar(255)
,`Precio` double(8,2)
,`Descripcion` varchar(255)
,`IdUser` varchar(255)
,`Ubicacion` varchar(255)
,`EstadoDelAnuncio` int(100)
,`Visibilidad` int(100)
,`SYS_INSERTED` date
,`Estado` varchar(255)
,`NombreCategoria` varchar(255)
,`FOTO1` varchar(255)
,`FOTO2` varchar(255)
,`FOTO3` varchar(255)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vistaanuncioconfoto`
--
DROP TABLE IF EXISTS `vistaanuncioconfoto`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vistaanuncioconfoto`  AS SELECT `a`.`IdAnuncio` AS `IdAnuncio`, `a`.`Categoria` AS `Categoria`, `a`.`Nombre` AS `Nombre`, `a`.`Precio` AS `Precio`, `a`.`Descripcion` AS `Descripcion`, `a`.`IdUser` AS `IdUser`, `a`.`Ubicacion` AS `Ubicacion`, `a`.`EstadoDelAnuncio` AS `EstadoDelAnuncio`, `a`.`Visibilidad` AS `Visibilidad`, `a`.`SYS_INSERTED` AS `SYS_INSERTED`, `e`.`Estado` AS `Estado`, `c`.`Nombre` AS `NombreCategoria`, max(case when `f`.`NumeroFoto` = 1 then `f`.`Fichero` end) AS `FOTO1`, max(case when `f`.`NumeroFoto` = 2 then `f`.`Fichero` end) AS `FOTO2`, max(case when `f`.`NumeroFoto` = 3 then `f`.`Fichero` end) AS `FOTO3` FROM (((`anuncio` `a` left join `foto` `f` on(`a`.`IdAnuncio` = `f`.`IdAnuncio`)) left join `estadomaterial` `e` on(`e`.`IdEstado` = `a`.`EstadoDelAnuncio`)) left join `categoria` `c` on(`c`.`IdCategoria` = `a`.`Categoria`)) GROUP BY `a`.`IdAnuncio`, `a`.`Categoria`, `a`.`Nombre`, `a`.`Precio`, `a`.`Descripcion`, `a`.`IdUser`, `a`.`Ubicacion`, `a`.`EstadoDelAnuncio`, `a`.`Visibilidad`, `a`.`SYS_INSERTED`, `e`.`Estado`, `c`.`Nombre` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `anuncio`
--
ALTER TABLE `anuncio`
  ADD PRIMARY KEY (`IdAnuncio`),
  ADD KEY `Categoria` (`Categoria`),
  ADD KEY `IdUser` (`IdUser`),
  ADD KEY `EstadoDelAnuncio` (`EstadoDelAnuncio`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`IdCategoria`);

--
-- Indices de la tabla `estadomaterial`
--
ALTER TABLE `estadomaterial`
  ADD PRIMARY KEY (`IdEstado`);

--
-- Indices de la tabla `foto`
--
ALTER TABLE `foto`
  ADD PRIMARY KEY (`IdFoto`),
  ADD KEY `IdAnuncio` (`IdAnuncio`);

--
-- Indices de la tabla `historial`
--
ALTER TABLE `historial`
  ADD PRIMARY KEY (`IdHistorial`),
  ADD KEY `IdAnuncio` (`IdAnuncio`),
  ADD KEY `historial_ibfk_2` (`IdUser`);

--
-- Indices de la tabla `peticion`
--
ALTER TABLE `peticion`
  ADD PRIMARY KEY (`IdPeticion`),
  ADD KEY `IdAnuncio` (`IdAnuncio`),
  ADD KEY `IdUsuario` (`IdUsuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`Usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anuncio`
--
ALTER TABLE `anuncio`
  MODIFY `IdAnuncio` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `IdCategoria` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `foto`
--
ALTER TABLE `foto`
  MODIFY `IdFoto` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `historial`
--
ALTER TABLE `historial`
  MODIFY `IdHistorial` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `peticion`
--
ALTER TABLE `peticion`
  MODIFY `IdPeticion` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `anuncio`
--
ALTER TABLE `anuncio`
  ADD CONSTRAINT `anuncio_ibfk_1` FOREIGN KEY (`Categoria`) REFERENCES `categoria` (`IdCategoria`),
  ADD CONSTRAINT `anuncio_ibfk_2` FOREIGN KEY (`IdUser`) REFERENCES `usuario` (`Usuario`),
  ADD CONSTRAINT `anuncio_ibfk_3` FOREIGN KEY (`EstadoDelAnuncio`) REFERENCES `estadomaterial` (`IdEstado`);

--
-- Filtros para la tabla `foto`
--
ALTER TABLE `foto`
  ADD CONSTRAINT `foto_ibfk_1` FOREIGN KEY (`IdAnuncio`) REFERENCES `anuncio` (`IdAnuncio`);

--
-- Filtros para la tabla `historial`
--
ALTER TABLE `historial`
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`IdAnuncio`) REFERENCES `anuncio` (`IdAnuncio`),
  ADD CONSTRAINT `historial_ibfk_2` FOREIGN KEY (`IdUser`) REFERENCES `usuario` (`Usuario`);

--
-- Filtros para la tabla `peticion`
--
ALTER TABLE `peticion`
  ADD CONSTRAINT `peticion_ibfk_1` FOREIGN KEY (`IdAnuncio`) REFERENCES `anuncio` (`IdAnuncio`),
  ADD CONSTRAINT `peticion_ibfk_2` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`Usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
