-- Seed Data para Tienda de Ropa Online
-- Cuentas por defecto:
-- Admin: admin@tiendaropa.com / admin123
-- Vendedor 1: vendedor@tiendaropa.com / seller123
-- Vendedor 2: boutique@tiendaropa.com / seller123
-- Cliente: cliente@tiendaropa.com / client123

TRUNCATE TABLE payments, order_items, orders, product_images, product_variants, products, categories, users CASCADE;

-- 1. Insertar Usuarios
INSERT INTO users (id, name, email, password_hash, role, phone, is_active) VALUES
('10000000-0000-0000-0000-000000000001', 'Alejandro Admin', 'admin@tiendaropa.com', '$2y$12$VbNZxdgA.MyO5sBprmVWuOwq.IxrO755B1XbN8ZfijNz7MFd7tSJ2', 'ADMIN', '+52 55 1234 5678', TRUE),
('20000000-0000-0000-0000-000000000001', 'Carlos Urban Wear', 'vendedor@tiendaropa.com', '$2y$12$0DDeUDeKc8qN5xdETCXBEugr.fkl9wRcqQ4tq3GfXC1BELYSCeQ76', 'SELLER', '+52 55 8765 4321', TRUE),
('20000000-0000-0000-0000-000000000002', 'Elena Chic Boutique', 'boutique@tiendaropa.com', '$2y$12$0DDeUDeKc8qN5xdETCXBEugr.fkl9wRcqQ4tq3GfXC1BELYSCeQ76', 'SELLER', '+52 55 9988 7766', TRUE),
('30000000-0000-0000-0000-000000000001', 'Mariana Gómez', 'cliente@tiendaropa.com', '$2y$12$GUzw69mfHqqjbiCJbT4Tn.h9fBY7ORLIkaH9ZwJaiituzXKjXHgHu', 'CLIENT', '+52 55 3344 5566', TRUE),
('30000000-0000-0000-0000-000000000002', 'David Rodríguez', 'david@tiendaropa.com', '$2y$12$GUzw69mfHqqjbiCJbT4Tn.h9fBY7ORLIkaH9ZwJaiituzXKjXHgHu', 'CLIENT', '+52 55 6677 8899', TRUE);

-- 2. Insertar Categorías
INSERT INTO categories (id, name, slug, description, image_url) VALUES
(1, 'Hombre', 'hombre', 'Moda masculina contemporánea, casual, formal y de temporada.', 'https://images.unsplash.com/photo-1617137984095-74e4e5e3613f?auto=format&fit=crop&w=600&q=80'),
(2, 'Mujer', 'mujer', 'Tendencias exclusivas de moda femenina, vestidos, blusas y más.', 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=600&q=80'),
(3, 'Abrigos & Chaquetas', 'abrigos-chaquetas', 'Prendas de abrigo de alta calidad, trenchs, cazadoras y blazers.', 'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=600&q=80'),
(4, 'Calzado', 'calzado', 'Sneakers urbanos, botas de piel y calzado formal.', 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=600&q=80'),
(5, 'Accesorios', 'accesorios', 'Bolsos, cinturones, gafas de sol y bufandas artesanales.', 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?auto=format&fit=crop&w=600&q=80');

-- 3. Insertar Productos
INSERT INTO products (id, seller_id, category_id, name, slug, description, base_price, status) VALUES
('40000000-0000-0000-0000-000000000001', '20000000-0000-0000-0000-000000000001', 3, 'Chaqueta Biker de Cuero Premium', 'chaqueta-biker-cuero-premium', 'Chaqueta de cuero genuino vacuno con acabados metálicos en níquel pulido, forro térmico satinado y cremalleras asimétricas de alta resistencia. Corte ajustado moderno para un estilo sofisticado y atemporal.', 189.99, 'ACTIVE'),
('40000000-0000-0000-0000-000000000002', '20000000-0000-0000-0000-000000000002', 2, 'Vestido Midi Floral de Seda', 'vestido-midi-floral-seda', 'Vestido midi confeccionado en mezcla de seda natural con estampado botánico exclusivo. Escote en V, cintura elástica adaptable y falda fluida con movimiento elegante.', 129.50, 'ACTIVE'),
('40000000-0000-0000-0000-000000000003', '20000000-0000-0000-0000-000000000001', 1, 'Camisa Oxford Algodón Egipcio', 'camisa-oxford-algodon-egipcio', 'Camisa básica de corte regular fit en 100% algodón egipcio peinado. Cuello con botones ocultos, botones de nácar y acabado easy-iron.', 64.00, 'ACTIVE'),
('40000000-0000-0000-0000-000000000004', '20000000-0000-0000-0000-000000000002', 2, 'Blazer Oversize Lana Merino', 'blazer-oversize-lana-merino', 'Blazer estructurado de silueta holgada en tejido cálido de pura lana virgen merino. Doble abotonadura frontal y bolsillos de ojal.', 155.00, 'ACTIVE'),
('40000000-0000-0000-0000-000000000005', '20000000-0000-0000-0000-000000000001', 4, 'Sneakers Minimalistas de Piel', 'sneakers-minimalistas-piel', 'Zapatillas de piel blanca curtida de manera ecológica con suela de caucho vulcanizado cosida a mano. Plantilla con memoria amortiguada.', 110.00, 'ACTIVE'),
('40000000-0000-0000-0000-000000000006', '20000000-0000-0000-0000-000000000002', 5, 'Bolso Tote Bag Cuero Genuino', 'bolso-tote-cuero-genuino', 'Bolso amplio de mano y hombro con compartimento para portátil de 14 pulgadas. Incluye neceser interior desmontable con cremallera.', 145.00, 'ACTIVE'),
('40000000-0000-0000-0000-000000000007', '20000000-0000-0000-0000-000000000001', 1, 'Pantalón Chino Slim Fit', 'pantalon-chino-slim-fit', 'Pantalón chino en sarga de algodón elástico para máximo confort durante todo el día. Cierre con cremallera y botón de cuerno.', 58.00, 'ACTIVE'),
('40000000-0000-0000-0000-000000000008', '20000000-0000-0000-0000-000000000002', 3, 'Trench Coat Clásico Impermeable', 'trench-coat-clasico-impermeable', 'Gabardina clásica cruzada con cinturón de hebilla metálica, solapas anchas y tejido hidrófugo de alto rendimiento.', 210.00, 'ACTIVE');

-- 4. Variantes de Productos
INSERT INTO product_variants (id, product_id, size, color, color_code, sku, stock, additional_price) VALUES
-- Chaqueta Biker
('50000000-0000-0000-0000-000000000001', '40000000-0000-0000-0000-000000000001', 'S', 'Negro Jet', '#111111', 'BIK-BLK-S', 12, 0.00),
('50000000-0000-0000-0000-000000000002', '40000000-0000-0000-0000-000000000001', 'M', 'Negro Jet', '#111111', 'BIK-BLK-M', 18, 0.00),
('50000000-0000-0000-0000-000000000003', '40000000-0000-0000-0000-000000000001', 'L', 'Negro Jet', '#111111', 'BIK-BLK-L', 8, 0.00),
('50000000-0000-0000-0000-000000000004', '40000000-0000-0000-0000-000000000001', 'M', 'Café Cognac', '#5c3a21', 'BIK-BRN-M', 6, 15.00),

-- Vestido Midi
('50000000-0000-0000-0000-000000000005', '40000000-0000-0000-0000-000000000002', 'XS', 'Verde Oliva', '#4b5320', 'VES-GRN-XS', 5, 0.00),
('50000000-0000-0000-0000-000000000006', '40000000-0000-0000-0000-000000000002', 'S', 'Verde Oliva', '#4b5320', 'VES-GRN-S', 15, 0.00),
('50000000-0000-0000-0000-000000000007', '40000000-0000-0000-0000-000000000002', 'M', 'Verde Oliva', '#4b5320', 'VES-GRN-M', 10, 0.00),

-- Camisa Oxford
('50000000-0000-0000-0000-000000000008', '40000000-0000-0000-0000-000000000003', 'S', 'Azul Cielo', '#87ceeb', 'CAM-BLU-S', 20, 0.00),
('50000000-0000-0000-0000-000000000009', '40000000-0000-0000-0000-000000000003', 'M', 'Azul Cielo', '#87ceeb', 'CAM-BLU-M', 25, 0.00),
('50000000-0000-0000-0000-000000000010', '40000000-0000-0000-0000-000000000003', 'L', 'Blanco Puro', '#ffffff', 'CAM-WHT-L', 14, 0.00),

-- Blazer Oversize
('50000000-0000-0000-0000-000000000011', '40000000-0000-0000-0000-000000000004', 'S', 'Gris Antracita', '#383838', 'BLA-GRY-S', 9, 0.00),
('50000000-0000-0000-0000-000000000012', '40000000-0000-0000-0000-000000000004', 'M', 'Gris Antracita', '#383838', 'BLA-GRY-M', 11, 0.00),

-- Sneakers
('50000000-0000-0000-0000-000000000013', '40000000-0000-0000-0000-000000000005', '41', 'Blanco / Marfil', '#fdfbf7', 'SNK-WHT-41', 7, 0.00),
('50000000-0000-0000-0000-000000000014', '40000000-0000-0000-0000-000000000005', '42', 'Blanco / Marfil', '#fdfbf7', 'SNK-WHT-42', 16, 0.00),
('50000000-0000-0000-0000-000000000015', '40000000-0000-0000-0000-000000000005', '43', 'Blanco / Marfil', '#fdfbf7', 'SNK-WHT-43', 9, 0.00),

-- Tote Bag
('50000000-0000-0000-0000-000000000016', '40000000-0000-0000-0000-000000000006', 'Única', 'Camel Tostado', '#c19a6b', 'TOT-CAM-U', 18, 0.00),

-- Pantalón Chino
('50000000-0000-0000-0000-000000000017', '40000000-0000-0000-0000-000000000007', '30', 'Beige Arena', '#d2b48c', 'CHI-BEI-30', 14, 0.00),
('50000000-0000-0000-0000-000000000018', '40000000-0000-0000-0000-000000000007', '32', 'Beige Arena', '#d2b48c', 'CHI-BEI-32', 22, 0.00),

-- Trench Coat
('50000000-0000-0000-0000-000000000019', '40000000-0000-0000-0000-000000000008', 'S', 'Beige Clásico', '#e3dac9', 'TRN-BEI-S', 4, 0.00),
('50000000-0000-0000-0000-000000000020', '40000000-0000-0000-0000-000000000008', 'M', 'Beige Clásico', '#e3dac9', 'TRN-BEI-M', 8, 0.00);

-- 5. Imágenes de Productos
INSERT INTO product_images (id, product_id, image_path, is_primary, sort_order) VALUES
-- Chaqueta Biker
('60000000-0000-0000-0000-000000000001', '40000000-0000-0000-0000-000000000001', 'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=800&q=80', TRUE, 1),
('60000000-0000-0000-0000-000000000002', '40000000-0000-0000-0000-000000000001', 'https://images.unsplash.com/photo-1521223890158-f9f7c3d5d504?auto=format&fit=crop&w=800&q=80', FALSE, 2),

-- Vestido Midi
('60000000-0000-0000-0000-000000000003', '40000000-0000-0000-0000-000000000002', 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?auto=format&fit=crop&w=800&q=80', TRUE, 1),
('60000000-0000-0000-0000-000000000004', '40000000-0000-0000-0000-000000000002', 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=800&q=80', FALSE, 2),

-- Camisa Oxford
('60000000-0000-0000-0000-000000000005', '40000000-0000-0000-0000-000000000003', 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?auto=format&fit=crop&w=800&q=80', TRUE, 1),
('60000000-0000-0000-0000-000000000006', '40000000-0000-0000-0000-000000000003', 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?auto=format&fit=crop&w=800&q=80', FALSE, 2),

-- Blazer Oversize
('60000000-0000-0000-0000-000000000007', '40000000-0000-0000-0000-000000000004', 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=800&q=80', TRUE, 1),

-- Sneakers
('60000000-0000-0000-0000-000000000008', '40000000-0000-0000-0000-000000000005', 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?auto=format&fit=crop&w=800&q=80', TRUE, 1),

-- Tote Bag
('60000000-0000-0000-0000-000000000009', '40000000-0000-0000-0000-000000000006', 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=800&q=80', TRUE, 1),

-- Pantalón Chino
('60000000-0000-0000-0000-000000000010', '40000000-0000-0000-0000-000000000007', 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?auto=format&fit=crop&w=800&q=80', TRUE, 1),

-- Trench Coat
('60000000-0000-0000-0000-000000000011', '40000000-0000-0000-0000-000000000008', 'https://images.unsplash.com/photo-1544441893-675973e31985?auto=format&fit=crop&w=800&q=80', TRUE, 1);

-- 6. Insertar Pedidos y Pagos Históricos para alimentar reportes
INSERT INTO orders (id, customer_id, order_number, status, total_amount, tax_amount, shipping_cost, shipping_address, created_at) VALUES
('70000000-0000-0000-0000-000000000001', '30000000-0000-0000-0000-000000000001', 'ORD-2026-1001', 'PAID', 319.49, 44.07, 0.00, '{"street":"Av. Reforma 450, Piso 8","city":"Ciudad de México","state":"CDMX","zip":"06600","recipient":"Mariana Gómez","phone":"5533445566"}'::jsonb, NOW() - INTERVAL '3 days'),
('70000000-0000-0000-0000-000000000002', '30000000-0000-0000-0000-000000000002', 'ORD-2026-1002', 'PAID', 220.00, 30.34, 0.00, '{"street":"Calle Durango 120","city":"Guadalajara","state":"Jalisco","zip":"44100","recipient":"David Rodríguez","phone":"5566778899"}'::jsonb, NOW() - INTERVAL '2 days'),
('70000000-0000-0000-0000-000000000003', '30000000-0000-0000-0000-000000000001', 'ORD-2026-1003', 'PAID', 155.00, 21.38, 0.00, '{"street":"Av. Reforma 450, Piso 8","city":"Ciudad de México","state":"CDMX","zip":"06600","recipient":"Mariana Gómez","phone":"5533445566"}'::jsonb, NOW() - INTERVAL '5 hours');

-- Líneas de pedido
INSERT INTO order_items (id, order_id, product_variant_id, product_name, size, color, unit_price, quantity, subtotal) VALUES
('80000000-0000-0000-0000-000000000001', '70000000-0000-0000-0000-000000000001', '50000000-0000-0000-0000-000000000002', 'Chaqueta Biker de Cuero Premium', 'M', 'Negro Jet', 189.99, 1, 189.99),
('80000000-0000-0000-0000-000000000002', '70000000-0000-0000-0000-000000000001', '50000000-0000-0000-0000-000000000006', 'Vestido Midi Floral de Seda', 'S', 'Verde Oliva', 129.50, 1, 129.50),
('80000000-0000-0000-0000-000000000003', '70000000-0000-0000-0000-000000000002', '50000000-0000-0000-0000-000000000014', 'Sneakers Minimalistas de Piel', '42', 'Blanco / Marfil', 110.00, 2, 220.00),
('80000000-0000-0000-0000-000000000004', '70000000-0000-0000-0000-000000000003', '50000000-0000-0000-0000-000000000012', 'Blazer Oversize Lana Merino', 'M', 'Gris Antracita', 155.00, 1, 155.00);

-- Pagos aprobados correspondientes
INSERT INTO payments (id, order_id, gateway, transaction_reference, amount, status, card_last4, payment_method, metadata, created_at) VALUES
('90000000-0000-0000-0000-000000000001', '70000000-0000-0000-0000-000000000001', 'SIMULATED', 'TXN-9842104-VSA', 319.49, 'APPROVED', '4242', 'Credit Card (Visa)', '{"auth_code":"AUTH88912","network":"Visa"}'::jsonb, NOW() - INTERVAL '3 days'),
('90000000-0000-0000-0000-000000000002', '70000000-0000-0000-0000-000000000002', 'STRIPE', 'ch_3NabcXYZ1234567890', 220.00, 'APPROVED', '5555', 'Credit Card (Mastercard)', '{"charge_id":"ch_3NabcXYZ1234567890","brand":"Mastercard"}'::jsonb, NOW() - INTERVAL '2 days'),
('90000000-0000-0000-0000-000000000003', '70000000-0000-0000-0000-000000000003', 'SIMULATED', 'TXN-4491028-AMX', 155.00, 'APPROVED', '8431', 'Credit Card (Amex)', '{"auth_code":"AUTH10294","network":"Amex"}'::jsonb, NOW() - INTERVAL '5 hours');
