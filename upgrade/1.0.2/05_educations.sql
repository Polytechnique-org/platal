-- Fixes error about duplicated Stuttgart University.
UPDATE IGNORE  profile_education_degree
          SET  eduid = 51
        WHERE  eduid = 212;
UPDATE  profile_education
   SET  eduid = 51
 WHERE  eduid = 212;
DELETE FROM profile_education_enum WHERE id = 212;

-- Inserts missing couple (education, degree) required by AX data.
INSERT IGNORE INTO  profile_education_degree (eduid, degreeid)
            VALUES  (184, 1),
                    (267, 1),
                    (247, 1),
                    (189, 1),
                    (190, 1),
                    (191, 1),
                    (192, 1),
                    (244, 1),
                    (193, 1),
                    (245, 1),
                    (194, 1),
                    (243, 1),
                    (195, 1),
                    (196, 1),
                    (197, 1),
                    (246, 1),
                    (205, 1),
                    (183, 1),
                    (233, 2),
                    (236, 2),
                    (187, 2),
                    (235, 2),
                    (188, 2),
                    (185, 2),
                    (238, 2),
                    (202, 2),
                    (46, 2),
                    (51, 2),
                    (213, 2),
                    (240, 2),
                    (228, 4),
                    (268, 4),
                    (218, 4),
                    (232, 4),
                    (226, 4),
                    (219, 4),
                    (229, 4),
                    (151, 4),
                    (175, 4),
                    (214, 4),
                    (133, 4),
                    (69, 4),
                    (241, 4),
                    (1, 4),
                    (127, 5),
                    (63, 5),
                    (222, 5),
                    (230, 5),
                    (217, 5),
                    (215, 5),
                    (220, 5),
                    (164, 5),
                    (221, 5),
                    (265, 5),
                    (14, 5),
                    (266, 5),
                    (237, 5),
                    (231, 5),
                    (18, 5),
                    (241, 5),
                    (259, 6),
                    (231, 6),
                    (178, 6),
                    (145, 6),
                    (85, 6),
                    (109, 7),
                    (241, 7),
                    (140, 8),
                    (208, 8),
                    (91, 8),
                    (23, 8),
                    (224, 8),
                    (239, 8),
                    (25, 8),
                    (209, 8),
                    (223, 8),
                    (61, 8),
                    (171, 8),
                    (204, 8),
                    (242, 8),
                    (108, 11),
                    (252, 11),
                    (151, 14),
                    (234, 14),
                    (108, 14),
                    (91, 14),
                    (92, 14),
                    (62, 16),
                    (22, 16),
                    (225, 16),
                    (84, 16),
                    (206, 16),
                    (92, 17),
                    (92, 18),
                    (210, 18),
                    (71, 18),
                    (83, 19),
                    (216, 21),
                    (119, 21),
                    (104, 22),
                    (20, 24),
                    (23, 25),
                    (99, 27),
                    (12, 28),
                    (97, 31),
                    (32, 31),
                    (23, 31),
                    (31, 33),
                    (24, 33),
                    (20, 33),
                    (62, 33),
                    (86, 33),
                    (69, 33),
                    (207, 33),
                    (27, 33),
                    (110, 33),
                    (30, 33),
                    (12, 33),
                    (75, 33),
                    (79, 33),
                    (128, 33),
                    (175, 33),
                    (28, 33),
                    (166, 33),
                    (1, 33),
                    (74, 33),
                    (65, 33),
                    (124, 33),
                    (82, 33),
                    (43, 33),
                    (72, 33),
                    (46, 33),
                    (77, 33),
                    (22, 33),
                    (81, 33),
                    (93, 33),
                    (76, 33),
                    (92, 33),
                    (145, 33);

-- vim:set syntax=mysql:
