#Users demodata
REPLACE INTO `oxuser` SET
    OXID = 'unzeruser',
    OXACTIVE = 1,
    OXRIGHTS = 'user',
    OXSHOPID = 1,
    OXUSERNAME = 'unzeruser@oxid-esales.dev',
    OXPASSWORD = '$2y$10$tJd1YkFr2y4kUmojqa6NPuHrcMzZmxc9mh4OWQcLONfHg4WXzbtlu',
    OXPASSSALT = '',
    OXFNAME = 'TestUserName',
    OXLNAME = 'TestUserSurname',
    OXSTREET = 'Musterstr.',
    OXSTREETNR = '12',
    OXCITY = 'City',
    OXZIP = '12345',
    OXSAL = 'Mr',
    OXCOUNTRYID = 'a7c40f631fc920687.20179984',
    OXBIRTHDATE = '1985-02-05 14:42:42',
    OXCREATE = '2021-02-05 14:42:42',
    OXREGISTER = '2021-02-05 14:42:42';

UPDATE oxuser
SET
    oxcountryid = 'a7c40f631fc920687.20179984',
    oxusername = 'admin@myoxideshop.com',
    oxpassword = '6cb4a34e1b66d3445108cd91b67f98b9',
    oxpasssalt = '6631386565336161636139613634663766383538633566623662613036636539'
WHERE OXUSERNAME='admin';

REPLACE INTO `oxuser` SET
    OXID = 'unzersecureuser',
    OXACTIVE = 1,
    OXRIGHTS = 'user',
    OXUSERNAME = 'unzersecureuser@oxid-esales.dev',
    OXPASSWORD = '$2y$10$tJd1YkFr2y4kUmojqa6NPuHrcMzZmxc9mh4OWQcLONfHg4WXzbtlu',
    OXPASSSALT = '',
    OXFNAME = 'Maximilian',
    OXLNAME = 'Mustermann',
    OXSTREET = 'Hugo-Junkers-Str.',
    OXSTREETNR = '3',
    OXCITY = 'Frankfurt am Main',
    OXZIP = '60386',
    OXSAL = 'Mr',
    OXCOUNTRYID = 'a7c40f631fc920687.20179984',
    OXBIRTHDATE = '1980-11-22',
    OXCREATE = '2021-02-05 14:42:42',
    OXREGISTER = '2021-02-05 14:42:42';


REPLACE INTO `oxcounters` SET
    OXIDENT = 'oxOrder',
    OXCOUNT = 10001,
    OXTIMESTAMP = '2024-05-13 12:04:58';

REPLACE INTO `oxcounters` SET
    OXIDENT = 'oxUnzerOrder',
    OXCOUNT = 5001,
    OXTIMESTAMP = '2024-05-13 12:04:58';
