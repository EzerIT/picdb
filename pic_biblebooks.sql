
UPDATE `pictures`.`pic_biblebooks` SET `english_abb` = 'Ex.' WHERE `pic_biblebooks`.`id` =2;



UPDATE pictures.pic_biblebooks SET english_abb='Gen.' WHERE english_name='Genesis';
UPDATE pictures.pic_biblebooks SET english_abb='Ex.' WHERE english_name='Exodus';
UPDATE pictures.pic_biblebooks SET english_abb='Lev.' WHERE english_name='Leviticus';
UPDATE pictures.pic_biblebooks SET english_abb='Num.' WHERE english_name='Numbers';
UPDATE pictures.pic_biblebooks SET english_abb='Deut.' WHERE english_name='Deuteronomy';
UPDATE pictures.pic_biblebooks SET english_abb='Josh.' WHERE english_name='Joshua';
UPDATE pictures.pic_biblebooks SET english_abb='Judg.' WHERE english_name='Judges';
UPDATE pictures.pic_biblebooks SET english_abb='Ruth' WHERE english_name='Ruth';
UPDATE pictures.pic_biblebooks SET english_abb='1 Sam.' WHERE english_name='1 Samuel';
UPDATE pictures.pic_biblebooks SET english_abb='1 Kgs.' WHERE english_name='1 Kings';
UPDATE pictures.pic_biblebooks SET english_abb='1 Chron.' WHERE english_name='1 Chronicles';
UPDATE pictures.pic_biblebooks SET english_abb='2 Sam.' WHERE english_name='2 Samuel';
UPDATE pictures.pic_biblebooks SET english_abb='2 Kgs.' WHERE english_name='2 Kings';
UPDATE pictures.pic_biblebooks SET english_abb='2 Chron.' WHERE english_name='2 Chronicles';
UPDATE pictures.pic_biblebooks SET english_abb='Ezra' WHERE english_name='Ezra';
UPDATE pictures.pic_biblebooks SET english_abb='Neh.' WHERE english_name='Nehemiah';
UPDATE pictures.pic_biblebooks SET english_abb='Esther' WHERE english_name='Esther';
UPDATE pictures.pic_biblebooks SET english_abb='Job' WHERE english_name='Job';
UPDATE pictures.pic_biblebooks SET english_abb='Ps.' WHERE english_name='Psalms';
UPDATE pictures.pic_biblebooks SET english_abb='Prov.' WHERE english_name='Proverbs';
UPDATE pictures.pic_biblebooks SET english_abb='Eccles.' WHERE english_name='Ecclesiastes';
UPDATE pictures.pic_biblebooks SET english_abb='Song' WHERE english_name='Song of Solomon';
UPDATE pictures.pic_biblebooks SET english_abb='Isa.' WHERE english_name='Isaiah';
UPDATE pictures.pic_biblebooks SET english_abb='Jer.' WHERE english_name='Jeremiah';
UPDATE pictures.pic_biblebooks SET english_abb='Lam.' WHERE english_name='Lamentations';
UPDATE pictures.pic_biblebooks SET english_abb='Ezek.' WHERE english_name='Ezekiel';
UPDATE pictures.pic_biblebooks SET english_abb='Dan.' WHERE english_name='Daniel';
UPDATE pictures.pic_biblebooks SET english_abb='Hos.' WHERE english_name='Hosea';
UPDATE pictures.pic_biblebooks SET english_abb='Joel' WHERE english_name='Joel';
UPDATE pictures.pic_biblebooks SET english_abb='Amos' WHERE english_name='Amos';
UPDATE pictures.pic_biblebooks SET english_abb='Obad.' WHERE english_name='Obadiah';
UPDATE pictures.pic_biblebooks SET english_abb='Jon.' WHERE english_name='Jonah';
UPDATE pictures.pic_biblebooks SET english_abb='Mic.' WHERE english_name='Micah';
UPDATE pictures.pic_biblebooks SET english_abb='Nah.' WHERE english_name='Nahum';
UPDATE pictures.pic_biblebooks SET english_abb='Hab.' WHERE english_name='Habakkuk';
UPDATE pictures.pic_biblebooks SET english_abb='Zeph.' WHERE english_name='Zephaniah';
UPDATE pictures.pic_biblebooks SET english_abb='Hag.' WHERE english_name='Haggai';
UPDATE pictures.pic_biblebooks SET english_abb='Zech.' WHERE english_name='Zechariah';
UPDATE pictures.pic_biblebooks SET english_abb='Mal.' WHERE english_name='Malachi';



UPDATE pictures.pic_biblebooks SET english_abb='Matt.' WHERE english_name='Matthew';
UPDATE pictures.pic_biblebooks SET english_abb='Mark' WHERE english_name='Mark';
UPDATE pictures.pic_biblebooks SET english_abb='Luke' WHERE english_name='Luke';
UPDATE pictures.pic_biblebooks SET english_abb='John' WHERE english_name='John';
UPDATE pictures.pic_biblebooks SET english_abb='Acts' WHERE english_name='Acts';
UPDATE pictures.pic_biblebooks SET english_abb='Rom.' WHERE english_name='Romans';
UPDATE pictures.pic_biblebooks SET english_abb='1 Cor.' WHERE english_name='1 Corinthians';
UPDATE pictures.pic_biblebooks SET english_abb='2 Cor.' WHERE english_name='2 Corinthians';
UPDATE pictures.pic_biblebooks SET english_abb='Gal.' WHERE english_name='Galatians';
UPDATE pictures.pic_biblebooks SET english_abb='Eph.' WHERE english_name='Ephesians';
UPDATE pictures.pic_biblebooks SET english_abb='Phil.' WHERE english_name='Philippians';
UPDATE pictures.pic_biblebooks SET english_abb='Col.' WHERE english_name='Colossians';
UPDATE pictures.pic_biblebooks SET english_abb='2 Thess.' WHERE english_name='2 Thessalonians';
UPDATE pictures.pic_biblebooks SET english_abb='2 Tim.' WHERE english_name='2 Timothy';
UPDATE pictures.pic_biblebooks SET english_abb='1 Thess.' WHERE english_name='1 Thessalonians';
UPDATE pictures.pic_biblebooks SET english_abb='1 Tim.' WHERE english_name='1 Timothy';
UPDATE pictures.pic_biblebooks SET english_abb='Titus' WHERE english_name='Titus';
UPDATE pictures.pic_biblebooks SET english_abb='Philem.' WHERE english_name='Philemon';
UPDATE pictures.pic_biblebooks SET english_abb='Heb.' WHERE english_name='Hebrews';
UPDATE pictures.pic_biblebooks SET english_abb='James' WHERE english_name='James';
UPDATE pictures.pic_biblebooks SET english_abb='1 Pet.' WHERE english_name='1 Peter';
UPDATE pictures.pic_biblebooks SET english_abb='1 John' WHERE english_name='1 John';
UPDATE pictures.pic_biblebooks SET english_abb='2 Pet.' WHERE english_name='2 Peter';
UPDATE pictures.pic_biblebooks SET english_abb='2 John' WHERE english_name='2 John';
UPDATE pictures.pic_biblebooks SET english_abb='3 John' WHERE english_name='3 John';
UPDATE pictures.pic_biblebooks SET english_abb='Jude' WHERE english_name='Jude';
UPDATE pictures.pic_biblebooks SET english_abb='Rev.' WHERE english_name='Revelation';


INSERT INTO `pic_biblebooks` (`id`, `internal`, `english_abb`, `english_name`, `wivu_name`) VALUES
(1, 'Gn', 'Gen.', 'Genesis', 'Genesis'),
(2, 'Ex', 'Ex', 'Exodus', 'Exodus'),
(3, 'Lv', 'Lv', 'Leviticus', 'Leviticus'),
(4, 'Nm', 'Nm', 'Numbers', 'Numbers'),
(5, 'Dt', 'Dt', 'Deuteronomy', 'Deuteronomy'),
(6, 'Jo', 'Jo', 'Joshua', 'Joshua'),
(7, 'Jgs', 'Jgs', 'Judges', 'Judges'),
(8, 'Ru', 'Ru', 'Ruth', 'Ruth'),
(9, 'ISm', '1 Sm', '1 Samuel', 'I_Samuel'),
(10, 'IISm', '2 Sm', '2 Samuel', 'II_Samuel'),
(11, 'IKgs', '1 Kgs', '1 Kings', 'I_Kings'),
(12, 'IIKgs', '2 Kgs', '2 Kings', 'II_Kings'),
(13, 'IChr', '1 Chr', '1 Chronicles', 'I_Chronicles'),
(14, 'IIChr', '2 Chr', '2 Chronicles', 'II_Chronicles'),
(15, 'Ezr', 'Ezr', 'Ezra', 'Ezra'),
(16, 'Neh', 'Neh', 'Nehemiah', 'Nehemiah'),
(17, 'Est', 'Est', 'Esther', 'Esther'),
(18, 'Jb', 'Jb', 'Job', 'Job'),
(19, 'Ps', 'Ps', 'Psalms', 'Psalms'),
(20, 'Prv', 'Prv', 'Proverbs', 'Proverbs'),
(21, 'Eccl', 'Eccl', 'Ecclesiastes', 'Ecclesiastes'),
(22, 'Sg', 'Sg', 'Song of Solomon', 'Canticles'),
(23, 'Is', 'Is', 'Isaiah', 'Isaiah'),
(24, 'Jer', 'Jer', 'Jeremiah', 'Jeremiah'),
(25, 'Lam', 'Lam', 'Lamentations', 'Lamentations'),
(26, 'Ez', 'Ez', 'Ezekiel', 'Ezekiel'),
(27, 'Dn', 'Dn', 'Daniel', 'Daniel'),
(28, 'Hos', 'Hos', 'Hosea', 'Hosea'),
(29, 'Jl', 'Jl', 'Joel', 'Joel'),
(30, 'Am', 'Am', 'Amos', 'Amos'),
(31, 'Ob', 'Ob', 'Obadiah', 'Obadiah'),
(32, 'Jon', 'Jon', 'Jonah', 'Jonah'),
(33, 'Mi', 'Mi', 'Micah', 'Micah'),
(34, 'Na', 'Na', 'Nahum', 'Nahum'),
(35, 'Hb', 'Hb', 'Habakkuk', 'Habakkuk'),
(36, 'Zep', 'Zep', 'Zephaniah', 'Zephaniah'),
(37, 'Hg', 'Hg', 'Haggai', 'Haggai'),
(38, 'Zec', 'Zec', 'Zechariah', 'Zechariah'),
(39, 'Mal', 'Mal', 'Malachi', 'Malachi'),
(40, 'Tb', 'Tb', 'Tobit', NULL),
(41, 'Jdt', 'Jdt', 'Judith', NULL),
(42, 'AddEst', 'Add Esth', 'Additions to Esther', NULL),
(43, 'IMc', '1 Mc', '1 Maccabees', NULL),
(44, 'IIMc', '2 Mc', '2 Maccabees', NULL),
(45, 'Ws', 'Ws', 'Wisdom of Solomon', NULL),
(46, 'Ecclus', 'Ecclus', 'Ecclesiasticus', NULL),
(47, 'PrOfMan', 'Pr of Man', 'Prayer of Manasseh', NULL),
(48, 'Bar', 'Bar', 'Baruch', NULL),
(49, 'LetJer', 'Let Jer', 'Letter of Jeremiah', NULL),
(50, 'AddDan', 'Add Dan', 'Additions to Daniel', NULL),
(51, 'Mt', 'Mt', 'Matthew', NULL),
(52, 'Mk', 'Mk', 'Mark', NULL),
(53, 'Lk', 'Lk', 'Luke', NULL),
(54, 'Jn', 'Jn', 'John', NULL),
(55, 'Acts', 'Acts', 'Acts of the Apostles', NULL),
(56, 'Rom', 'Rom', 'Romans', NULL),
(57, 'ICor', '1 Cor', '1 Corinthians', NULL),
(58, 'IICor', '2 Cor', '2 Corinthians', NULL),
(59, 'Gal', 'Gal', 'Galatians', NULL),
(60, 'Eph', 'Eph', 'Ephesians', NULL),
(61, 'Phil', 'Phil', 'Philippians', NULL),
(62, 'Col', 'Col', 'Colossians', NULL),
(63, 'IThes', '1 Thes', '1 Thessalonians', NULL),
(64, 'IIThes', '2 Thes', '2 Thessalonians', NULL),
(65, 'ITm', '1 Tm', '1 Timothy', NULL),
(66, 'IITm', '2 Tm', '2 Timothy', NULL),
(67, 'Ti', 'Ti', 'Titus', NULL),
(68, 'Phlm', 'Phlm', 'Philemon', NULL),
(69, 'Heb', 'Heb', ' Hebrews', NULL),
(70, 'Jas', 'Jas', 'James', NULL),
(71, 'IPt', '1 Pt', '1 Peter', NULL),
(72, 'IIPt', '2 Pt', '2 Peter', NULL),
(73, 'IJn', '1 Jn', '1 John', NULL),
(74, 'IIJn', '2 Jn', '2 John', NULL),
(75, 'IIIJn', '3 Jn', '3 John', NULL),
(76, 'Jude', 'Jude', 'Jude', NULL),
(77, 'Rv', 'Rv', 'Revelation', NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
