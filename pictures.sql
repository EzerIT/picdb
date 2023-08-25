-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 28, 2012 at 11:03 
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pictures`
--

-- --------------------------------------------------------

--
-- Table structure for table `pic_biblebooks`
--

CREATE TABLE IF NOT EXISTS `pic_biblebooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `internal` tinytext NOT NULL COMMENT 'Name used internally by system - must not contain spaces',
  `english_abb` tinytext NOT NULL COMMENT 'English abbreviation of name',
  `english_name` tinytext NOT NULL COMMENT 'English name',
  `wivu_name` tinytext COMMENT 'Name used by WIVU database',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='The names of the books of the Bible';

-- --------------------------------------------------------

--
-- Table structure for table `pic_bibleref`
--

CREATE TABLE IF NOT EXISTS `pic_bibleref` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `bookid` int(11) NOT NULL COMMENT 'ID of Bible book (biblebooks table)',
  `chapter` int(11) NOT NULL COMMENT 'Chapter',
  `verse_low` int(11) NOT NULL COMMENT 'First verse',
  `verse_high` int(11) NOT NULL COMMENT 'Last verse (9999 denotes end of chapter)',
  `picid` int(11) NOT NULL COMMENT 'ID of picture (photos table)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Links pictures to Bible references';

-- --------------------------------------------------------

--
-- Table structure for table `pic_catbibleref`
--

CREATE TABLE IF NOT EXISTS `pic_catbibleref` (
  `bookid` int(11) NOT NULL COMMENT 'ID of Bible book (biblebooks table)',
  `chapter` int(11) NOT NULL COMMENT 'Chapter',
  `verse_low` int(11) NOT NULL COMMENT 'First verse',
  `verse_high` int(11) NOT NULL COMMENT 'Last verse (9999 denotes end of chapter)',
  `catval_id` int(11) NOT NULL COMMENT 'ID of category value (catval table)',
  UNIQUE KEY `bookid` (`bookid`,`chapter`,`verse_low`,`verse_high`,`catval_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Links category values to Bible references';

-- --------------------------------------------------------

--
-- Table structure for table `pic_categories`
--

CREATE TABLE IF NOT EXISTS `pic_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `abb` tinytext NOT NULL COMMENT 'Abbreviated name (lower case alphanumeric, no spaces)',
  `name` tinytext NOT NULL COMMENT 'Category name',
  `isstring` tinyint(1) NOT NULL COMMENT '1 if the values are strings, 0 if the values are numbers',
  `display` tinyint(1) NOT NULL COMMENT '1 if this category is displayed in the search dialog, 0 otherwise',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Picture categories';

-- --------------------------------------------------------

--
-- Table structure for table `pic_catval`
--

CREATE TABLE IF NOT EXISTS `pic_catval` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `category` int(11) NOT NULL COMMENT 'ID of category (categories table)',
  `intval` int(11) DEFAULT NULL COMMENT 'Low value if categories.stringval=0',
  `intval_high` int(11) DEFAULT NULL COMMENT 'High value if categories.stringval=0 (NULL if value is not a range)',
  `stringval` tinytext COMMENT 'Low value if categories.stringval=1',
  `stringval_high` tinytext COMMENT 'High value if categories.stringval=1 (NULL if value is not a range)',
  `name` tinytext NOT NULL COMMENT 'English name of value',
  `name_da` tinytext COMMENT 'Danish name of value',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Values available for categories';

-- --------------------------------------------------------

--
-- Table structure for table `pic_photos`
--

CREATE TABLE IF NOT EXISTS `pic_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `pic_no` int(11) NOT NULL COMMENT 'Picture number (part of file name)',
  `filename` text NOT NULL COMMENT 'Filename',
  `width` int(11) NOT NULL COMMENT 'Picture width in pixels',
  `height` int(11) NOT NULL COMMENT 'Picture height in pixels',
  `date` datetime DEFAULT NULL COMMENT 'Date when picture was taken',
  `description` text COMMENT 'Description',
  `published` tinyint(1) NOT NULL COMMENT '1 if this picture has been published, 0 otherwise',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='List of photos';

-- --------------------------------------------------------

--
-- Table structure for table `pic_piccat`
--

CREATE TABLE IF NOT EXISTS `pic_piccat` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `picid` int(11) NOT NULL COMMENT 'ID of picture (photos table)',
  `catid` int(11) NOT NULL COMMENT 'ID of category (categories table)',
  `intval` int(11) DEFAULT NULL COMMENT 'Category value if categories.isstring=0',
  `stringval` tinytext COMMENT 'Category value if categories.isstring=1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Links category values to pictures';

-- --------------------------------------------------------

--
-- Table structure for table `pic_users`
--

CREATE TABLE IF NOT EXISTS `pic_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `username` varchar(32) COLLATE utf8_danish_ci NOT NULL COMMENT 'User name',
  `password` tinytext COLLATE utf8_danish_ci NOT NULL COMMENT 'Password hash',
  `first_name` varchar(64) COLLATE utf8_danish_ci NOT NULL COMMENT 'First name',
  `last_name` varchar(64) COLLATE utf8_danish_ci NOT NULL COMMENT 'Last name',
  `email` varchar(64) COLLATE utf8_danish_ci NOT NULL COMMENT 'E-mail address',
  `isadmin` tinyint(1) NOT NULL COMMENT '1 if user has administrator rights, 0 otherwise',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_danish_ci COMMENT='Authorised users';



-- --------------------------------------------------------

--
-- Table structure for table `pic_bibleurl`
--

CREATE TABLE IF NOT EXISTS `pic_bibleurl` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
  `bookid` int(11) NOT NULL COMMENT 'ID of Bible book (biblebooks table)',
  `chapter` int(11) NOT NULL COMMENT 'Chapter',
  `verse_low` int(11) NOT NULL COMMENT 'First verse',
  `verse_high` int(11) NOT NULL COMMENT 'Last verse (9999 denotes end of chapter)',
  `url` text NOT NULL COMMENT 'URL',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Links Bible references to URLs';



--
-- Dumping data for table `pic_biblebooks`
--

INSERT INTO `pic_biblebooks` (`id`, `internal`, `english_abb`, `english_name`, `wivu_name`) VALUES
(1, 'Gn', 'Gn', 'Genesis', 'Genesis'),
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
