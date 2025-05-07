-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-04-2025 a las 05:17:36
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema-posventa`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_gasto`
--

CREATE TABLE `categoria_gasto` (
  `id_categoria_gasto` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_gasto`
--

INSERT INTO `categoria_gasto` (`id_categoria_gasto`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, 'Insumos Carwash', 'Productos de limpieza para el servicio de lavado', 1, '2025-04-02 07:18:28'),
(2, 'Mantenimiento', 'Mantenimiento de equipos y local', 1, '2025-04-02 07:18:28'),
(3, 'Servicios Básicos', 'Agua, luz, internet, etc.', 1, '2025-04-02 07:18:28'),
(4, 'Compra Inventario', 'Compras para reabastecer la tienda', 1, '2025-04-02 07:18:28'),
(5, 'Reparaciones', 'Reparaciones de equipos y maquinaria', 1, '2025-04-02 07:18:28'),
(6, 'Otros', 'Otros gastos no categorizados', 1, '2025-04-02 07:18:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_producto`
--

CREATE TABLE `categoria_producto` (
  `id_categoria_producto` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_producto`
--

INSERT INTO `categoria_producto` (`id_categoria_producto`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, 'Bebidas', 'Agua, refrescos y otras bebidas', 1, '2025-04-02 07:18:28'),
(2, 'Snacks', 'Bocadillos, galletas y similares', 1, '2025-04-02 07:18:28'),
(3, 'Dulces', 'Chicles, caramelos y golosinas', 1, '2025-04-02 07:18:28'),
(4, 'Accesorios', 'Accesorios para vehículos', 1, '2025-04-02 07:18:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_producto`
--

CREATE TABLE `detalle_producto` (
  `id_detalle_producto` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_servicio`
--

CREATE TABLE `detalle_servicio` (
  `id_detalle_servicio` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_precio_servicio` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_servicio_extra`
--

CREATE TABLE `detalle_servicio_extra` (
  `id_detalle_servicio_extra` int(11) NOT NULL,
  `id_detalle_servicio` int(11) NOT NULL,
  `id_servicio_extra` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gasto`
--

CREATE TABLE `gasto` (
  `id_gasto` int(11) NOT NULL,
  `id_categoria_gasto` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_gasto` date NOT NULL,
  `comprobante` varchar(50) DEFAULT NULL,
  `es_extraordinario` tinyint(1) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nivel_servicio`
--

CREATE TABLE `nivel_servicio` (
  `id_nivel_servicio` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nivel_servicio`
--

INSERT INTO `nivel_servicio` (`id_nivel_servicio`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(1, 'Básico', 'Lavado exterior básico', 1, '2025-04-02 07:18:28'),
(2, 'Completo', 'Lavado exterior e interior completo', 1, '2025-04-02 07:18:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `precio_servicio`
--

CREATE TABLE `precio_servicio` (
  `id_precio_servicio` int(11) NOT NULL,
  `id_tipo_vehiculo` int(11) NOT NULL,
  `id_nivel_servicio` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `precio_servicio`
--

INSERT INTO `precio_servicio` (`id_precio_servicio`, `id_tipo_vehiculo`, `id_nivel_servicio`, `precio`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 2, 1, 15.00, 1, '2025-04-02 17:39:19', '2025-04-02 17:50:50'),
(2, 3, 2, 50.00, 1, '2025-04-02 17:47:30', '2025-04-02 17:47:30'),
(3, 4, 2, 20.00, 1, '2025-04-02 17:51:01', '2025-04-02 17:51:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `id_categoria_producto` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 5,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `id_categoria_producto`, `codigo`, `nombre`, `descripcion`, `precio_compra`, `precio_venta`, `stock`, `stock_minimo`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(3, 2, '0003', 'papitas lais ', 'papitas lais esta en un bolsa de pastico', 12.00, 1.50, 15, 3, 1, '2025-04-02 16:08:57', '2025-04-02 16:08:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_extra_tipo_vehiculo`
--

CREATE TABLE `servicios_extra_tipo_vehiculo` (
  `id_servicio_extra` int(11) NOT NULL,
  `id_tipo_vehiculo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio_extra`
--

CREATE TABLE `servicio_extra` (
  `id_servicio_extra` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicio_extra`
--

INSERT INTO `servicio_extra` (`id_servicio_extra`, `nombre`, `descripcion`, `precio`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Encerado', 'Aplicación de cera protectora', 25.00, 1, '2025-04-02 07:18:28', '2025-04-02 07:18:28'),
(2, 'Limpieza de Motor', 'Limpieza y desengrasado del motor', 35.00, 1, '2025-04-02 07:18:28', '2025-04-02 07:18:28'),
(3, 'Limpieza de Techo', 'Limpieza profunda del techo interior', 20.00, 1, '2025-04-02 07:18:28', '2025-04-02 07:18:28'),
(4, 'Limpieza de Asientos', 'Limpieza y aspirado profundo de asientos', 30.00, 1, '2025-04-02 07:18:28', '2025-04-02 07:18:28'),
(5, 'lavado ', 'lavado de todo e motokar y tambien agregar pulidor', 12.00, 1, '2025-04-03 21:53:16', '2025-04-03 21:53:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_vehiculo`
--

CREATE TABLE `tipo_vehiculo` (
  `id_tipo_vehiculo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_vehiculo`
--

INSERT INTO `tipo_vehiculo` (`id_tipo_vehiculo`, `nombre`, `descripcion`, `estado`, `fecha_creacion`) VALUES
(2, 'Camioneta', 'Camionetas, SUVs y pick-ups', 1, '2025-04-02 07:18:28'),
(3, 'Motokar', 'Mototaxis y vehículos de tres ruedas ya haciento', 1, '2025-04-02 07:18:28'),
(4, 'Furgón', 'Vehículos de carga y transporte pequeños', 1, '2025-04-02 07:18:28'),
(5, 'Moto Lineal', 'Motocicletas de dos ruedas', 1, '2025-04-02 07:18:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `id_venta` int(11) NOT NULL,
  `numero_comprobante` varchar(20) DEFAULT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `igv` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('PENDIENTE','COMPLETADO','ANULADO') DEFAULT 'COMPLETADO',
  `tipo_venta` enum('SOLO_PRODUCTO','SOLO_SERVICIO','MIXTO') NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_balance_mensual`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_balance_mensual` (
`mes` varchar(7)
,`ingresos_carwash` decimal(33,2)
,`ingresos_tienda` decimal(32,2)
,`ingresos_totales` decimal(34,2)
,`egresos_totales` decimal(54,2)
,`balance` decimal(55,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_egresos_mensuales`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_egresos_mensuales` (
`mes` varchar(7)
,`categoria` varchar(50)
,`total_gastos` decimal(32,2)
,`gastos_extraordinarios` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ingresos_carwash`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ingresos_carwash` (
`mes` varchar(7)
,`ingresos_servicios_base` decimal(32,2)
,`ingresos_servicios_extra` decimal(32,2)
,`total_ingresos` decimal(33,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ingresos_tienda`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ingresos_tienda` (
`mes` varchar(7)
,`total_ingresos` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_balance_mensual`
--
DROP TABLE IF EXISTS `v_balance_mensual`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_balance_mensual`  AS SELECT `meses`.`mes` AS `mes`, ifnull(`ic`.`total_ingresos`,0) AS `ingresos_carwash`, ifnull(`it`.`total_ingresos`,0) AS `ingresos_tienda`, ifnull(`ic`.`total_ingresos`,0) + ifnull(`it`.`total_ingresos`,0) AS `ingresos_totales`, ifnull(`em`.`total_gastos`,0) AS `egresos_totales`, ifnull(`ic`.`total_ingresos`,0) + ifnull(`it`.`total_ingresos`,0) - ifnull(`em`.`total_gastos`,0) AS `balance` FROM ((((select distinct date_format(`venta`.`fecha_hora`,'%Y-%m') AS `mes` from `venta` union select distinct date_format(`gasto`.`fecha_gasto`,'%Y-%m') AS `mes` from `gasto`) `meses` left join `v_ingresos_carwash` `ic` on(`meses`.`mes` = `ic`.`mes`)) left join `v_ingresos_tienda` `it` on(`meses`.`mes` = `it`.`mes`)) left join (select `v_egresos_mensuales`.`mes` AS `mes`,sum(`v_egresos_mensuales`.`total_gastos`) AS `total_gastos` from `v_egresos_mensuales` group by `v_egresos_mensuales`.`mes`) `em` on(`meses`.`mes` = `em`.`mes`)) ORDER BY `meses`.`mes` ASC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_egresos_mensuales`
--
DROP TABLE IF EXISTS `v_egresos_mensuales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_egresos_mensuales`  AS SELECT date_format(`g`.`fecha_gasto`,'%Y-%m') AS `mes`, `cg`.`nombre` AS `categoria`, sum(`g`.`monto`) AS `total_gastos`, sum(case when `g`.`es_extraordinario` = 1 then `g`.`monto` else 0 end) AS `gastos_extraordinarios` FROM (`gasto` `g` join `categoria_gasto` `cg` on(`g`.`id_categoria_gasto` = `cg`.`id_categoria_gasto`)) WHERE `g`.`estado` = 1 GROUP BY date_format(`g`.`fecha_gasto`,'%Y-%m'), `cg`.`nombre` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ingresos_carwash`
--
DROP TABLE IF EXISTS `v_ingresos_carwash`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ingresos_carwash`  AS SELECT date_format(`v`.`fecha_hora`,'%Y-%m') AS `mes`, sum(`ds`.`precio`) AS `ingresos_servicios_base`, sum(ifnull(`dse`.`precio`,0)) AS `ingresos_servicios_extra`, sum(`ds`.`precio`) + sum(ifnull(`dse`.`precio`,0)) AS `total_ingresos` FROM ((`venta` `v` left join `detalle_servicio` `ds` on(`v`.`id_venta` = `ds`.`id_venta`)) left join `detalle_servicio_extra` `dse` on(`ds`.`id_detalle_servicio` = `dse`.`id_detalle_servicio`)) WHERE `v`.`estado` = 'COMPLETADO' GROUP BY date_format(`v`.`fecha_hora`,'%Y-%m') ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ingresos_tienda`
--
DROP TABLE IF EXISTS `v_ingresos_tienda`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ingresos_tienda`  AS SELECT date_format(`v`.`fecha_hora`,'%Y-%m') AS `mes`, sum(`dp`.`subtotal`) AS `total_ingresos` FROM (`venta` `v` join `detalle_producto` `dp` on(`v`.`id_venta` = `dp`.`id_venta`)) WHERE `v`.`estado` = 'COMPLETADO' GROUP BY date_format(`v`.`fecha_hora`,'%Y-%m') ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria_gasto`
--
ALTER TABLE `categoria_gasto`
  ADD PRIMARY KEY (`id_categoria_gasto`);

--
-- Indices de la tabla `categoria_producto`
--
ALTER TABLE `categoria_producto`
  ADD PRIMARY KEY (`id_categoria_producto`);

--
-- Indices de la tabla `detalle_producto`
--
ALTER TABLE `detalle_producto`
  ADD PRIMARY KEY (`id_detalle_producto`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `detalle_servicio`
--
ALTER TABLE `detalle_servicio`
  ADD PRIMARY KEY (`id_detalle_servicio`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_precio_servicio` (`id_precio_servicio`);

--
-- Indices de la tabla `detalle_servicio_extra`
--
ALTER TABLE `detalle_servicio_extra`
  ADD PRIMARY KEY (`id_detalle_servicio_extra`),
  ADD KEY `id_detalle_servicio` (`id_detalle_servicio`),
  ADD KEY `id_servicio_extra` (`id_servicio_extra`);

--
-- Indices de la tabla `gasto`
--
ALTER TABLE `gasto`
  ADD PRIMARY KEY (`id_gasto`),
  ADD KEY `id_categoria_gasto` (`id_categoria_gasto`);

--
-- Indices de la tabla `nivel_servicio`
--
ALTER TABLE `nivel_servicio`
  ADD PRIMARY KEY (`id_nivel_servicio`);

--
-- Indices de la tabla `precio_servicio`
--
ALTER TABLE `precio_servicio`
  ADD PRIMARY KEY (`id_precio_servicio`),
  ADD KEY `id_tipo_vehiculo` (`id_tipo_vehiculo`),
  ADD KEY `id_nivel_servicio` (`id_nivel_servicio`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `id_categoria_producto` (`id_categoria_producto`);

--
-- Indices de la tabla `servicios_extra_tipo_vehiculo`
--
ALTER TABLE `servicios_extra_tipo_vehiculo`
  ADD PRIMARY KEY (`id_servicio_extra`,`id_tipo_vehiculo`),
  ADD KEY `id_tipo_vehiculo` (`id_tipo_vehiculo`);

--
-- Indices de la tabla `servicio_extra`
--
ALTER TABLE `servicio_extra`
  ADD PRIMARY KEY (`id_servicio_extra`);

--
-- Indices de la tabla `tipo_vehiculo`
--
ALTER TABLE `tipo_vehiculo`
  ADD PRIMARY KEY (`id_tipo_vehiculo`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`id_venta`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria_gasto`
--
ALTER TABLE `categoria_gasto`
  MODIFY `id_categoria_gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `categoria_producto`
--
ALTER TABLE `categoria_producto`
  MODIFY `id_categoria_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalle_producto`
--
ALTER TABLE `detalle_producto`
  MODIFY `id_detalle_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_servicio`
--
ALTER TABLE `detalle_servicio`
  MODIFY `id_detalle_servicio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_servicio_extra`
--
ALTER TABLE `detalle_servicio_extra`
  MODIFY `id_detalle_servicio_extra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gasto`
--
ALTER TABLE `gasto`
  MODIFY `id_gasto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nivel_servicio`
--
ALTER TABLE `nivel_servicio`
  MODIFY `id_nivel_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `precio_servicio`
--
ALTER TABLE `precio_servicio`
  MODIFY `id_precio_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `servicio_extra`
--
ALTER TABLE `servicio_extra`
  MODIFY `id_servicio_extra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tipo_vehiculo`
--
ALTER TABLE `tipo_vehiculo`
  MODIFY `id_tipo_vehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_producto`
--
ALTER TABLE `detalle_producto`
  ADD CONSTRAINT `detalle_producto_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `venta` (`id_venta`),
  ADD CONSTRAINT `detalle_producto_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `detalle_servicio`
--
ALTER TABLE `detalle_servicio`
  ADD CONSTRAINT `detalle_servicio_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `venta` (`id_venta`),
  ADD CONSTRAINT `detalle_servicio_ibfk_2` FOREIGN KEY (`id_precio_servicio`) REFERENCES `precio_servicio` (`id_precio_servicio`);

--
-- Filtros para la tabla `detalle_servicio_extra`
--
ALTER TABLE `detalle_servicio_extra`
  ADD CONSTRAINT `detalle_servicio_extra_ibfk_1` FOREIGN KEY (`id_detalle_servicio`) REFERENCES `detalle_servicio` (`id_detalle_servicio`),
  ADD CONSTRAINT `detalle_servicio_extra_ibfk_2` FOREIGN KEY (`id_servicio_extra`) REFERENCES `servicio_extra` (`id_servicio_extra`);

--
-- Filtros para la tabla `gasto`
--
ALTER TABLE `gasto`
  ADD CONSTRAINT `gasto_ibfk_1` FOREIGN KEY (`id_categoria_gasto`) REFERENCES `categoria_gasto` (`id_categoria_gasto`);

--
-- Filtros para la tabla `precio_servicio`
--
ALTER TABLE `precio_servicio`
  ADD CONSTRAINT `precio_servicio_ibfk_1` FOREIGN KEY (`id_tipo_vehiculo`) REFERENCES `tipo_vehiculo` (`id_tipo_vehiculo`),
  ADD CONSTRAINT `precio_servicio_ibfk_2` FOREIGN KEY (`id_nivel_servicio`) REFERENCES `nivel_servicio` (`id_nivel_servicio`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_categoria_producto`) REFERENCES `categoria_producto` (`id_categoria_producto`);

--
-- Filtros para la tabla `servicios_extra_tipo_vehiculo`
--
ALTER TABLE `servicios_extra_tipo_vehiculo`
  ADD CONSTRAINT `servicios_extra_tipo_vehiculo_ibfk_1` FOREIGN KEY (`id_servicio_extra`) REFERENCES `servicio_extra` (`id_servicio_extra`) ON DELETE CASCADE,
  ADD CONSTRAINT `servicios_extra_tipo_vehiculo_ibfk_2` FOREIGN KEY (`id_tipo_vehiculo`) REFERENCES `tipo_vehiculo` (`id_tipo_vehiculo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
