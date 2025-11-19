-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Stř 19. lis 2025, 22:57
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Databáze: `krs`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `excerpt` text DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `articles`
--

INSERT INTO `articles` (`id`, `title`, `slug`, `content`, `excerpt`, `author_id`, `status`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 'Lorem ipsum dolor sit amet consectetuer ', 'lorem-ipsum-dolor-sit-amet-consectetuer--1758668587', '<p>Nibh nec commodo Ut sed fames et elit sed aliquam tellus. Wisi scelerisque et elit et nascetur malesuada Phasellus Nam magna cursus. Id nunc egestas urna justo suscipit Pellentesque tellus wisi Sed Curabitur. Turpis ligula nulla lacus enim non vel congue faucibus wisi Pellentesque. Orci aliquam Donec consequat tristique convallis Integer id ut libero vel. Sociis nisl venenatis condimentum congue Nullam vitae Maecenas nec orci pede. Suscipit.</p>\r\n<p>Est augue vitae vitae in lacus vitae at auctor in mi. Dapibus tincidunt felis ligula Quisque semper Donec interdum In felis id. Suscipit Aenean convallis Mauris felis Curabitur eleifend gravida pharetra venenatis nunc. Orci porttitor laoreet nisl metus quis Vestibulum Pellentesque In eros Curabitur. Tristique senectus pellentesque sit justo Quisque Nam odio mauris Fusce Maecenas. Platea.</p>\r\n<p>Massa odio risus porttitor montes velit quis vitae ac consequat ut. Semper Aenean ipsum mus ligula tristique Phasellus mauris ultrices accumsan Nunc. Cras Ut at vel Vestibulum ornare ac senectus ante ut tellus. Elit Aliquam tempus Nullam interdum tempor Curabitur nibh porta non wisi. Eros Cras at consectetuer amet accumsan pellentesque.</p>\r\n\r\n', 'Lorem ipsum dolor sit amet consectetuer eros id mus nulla Nulla. Nam Curabitur dignissim urna orci eu orci Lorem quis congue semper. Id faucibus Vivamus nunc adipiscing euismod eu cursus velit et pretium. Vestibulum sit pellentesque pede Sed metus natoque Aenean Vestibulum mattis Nunc. Maecenas Vestibulum ac fermentum ellentesque Sed', 1, 'published', '2025-09-23 23:03:07', '2025-09-19 00:09:00', '2025-10-04 22:36:44', NULL),
(7, 'Soběstačný domov', 'sobestacny-domov-1759618485', 'aučíte se, jak si vyrobit sýr na gril, tvaroh, čerstvé farmářské sýry a dám vám i návody na poněkud složitější, zrající sýry.', 'Vítejte v kurzu, mám ohromnou radost, že jste tady, že jste se rozhodli udělat ten první krůček k soběstačnosti a že právě já můžu být vaší průvodkyní! 🙂    Začněte tím, že si přehrajete video vpravo, ve kterém máte úvodní praktické informace.  Kdybyste cokoliv potřebovali, neváhejte mě kontaktovat, jsem tu pro vás. Přeju hodně štěstí při výrobě!    Jana ', 1, 'published', '2025-10-04 22:54:45', '2025-09-29 21:47:45', '2025-10-04 22:54:45', NULL),
(8, 'Prohlížeč Firefox', 'prohlizec-firefox-1759619166', '\r\n\r\nNa internet se připojujte s menším množstvím rušivých vlivů, hluku a stresu. Berte nás jako závan čerstvého vzduchu.\r\nNastavte si Firefox jako svůj výchozí prohlížeč.\r\nStáhnout Firefox\r\nFirefox a soukromí\r\nDalší verze a jazyky\r\nStránky podpory prohlížeče Firefox\r\nFirefox na počítači i mobilu.\r\nNejnovější funkce Firefoxu\r\n\r\n    Šťastný přepínač.\r\n    Ztlumení hluku\r\n\r\n    Blokujte reklamy a vylepšete své soukromí pomocí přizpůsobitelných nastavení a spousty rozšíření ke stažení.\r\n    Více kurzorů.\r\n    Multitasking?\r\n\r\n    Přejděte do režimu čtení, vysouvejte videa a zlepšete si organizaci pomocí svislých panelů a skupin panelů.\r\n', 'Převezměte kontrolu nad svým internetem\r\n', 1, 'published', '2025-10-04 23:06:06', '2025-10-04 23:05:51', '2025-10-04 23:06:06', NULL),
(9, 'Seznam nově zvolených poslanců', 'seznam-nove-zvolenych-poslancu-1759695672', 'Hlavním vítězem voleb je hnutí ANO Andreje Babiše s téměř 35 procenty hlasů, což mu přinese 80 poslaneckých křesel. To je o osm poslanců více, než mělo doposud. ANO se tak počtem poslanců stává nejsilnější parlamentní stranou od roku 2006, kdy ODS získala 81 poslanců.\r\n\r\nOptikou největšího nárůstu počtu poslanců jsou ovšem skrytými vítězi Piráti. Zatímco v roce 2021 obsadili čtyři poslanecké posty, nyní budou mít 18 křesel. Celkově 14 nových mandátů je více, než kolik získali letošní sněmovní nováčkové Motoristé. Ti v nové Poslanecké sněmovně obsadí 13 křesel.\r\nNové tváře Sněmovny: Turek, Ševčík, Šichtařová nebo „zelená“ Svárovská\r\nvčera 20:25\r\n\r\nKoalice Spolu naopak tratí 19 poslanců. Po volbách v roce 2021 dosáhla na 71 postů, aktuálně jim propočtu přisuzují zhruba 52 poslanců. Starostové pak tratili zhruba 11 poslanců a SPD pět postů.\r\n\r\nMandáty získá také deset lidí ve věku do 29 let. To je nejvíce od voleb v roce 2010, kdy se do Sněmovny dostalo dvanáct dvacátníků.\r\n\r\nLetošní volby do Poslanecké sněmovny navíc přinesly mimořádně silný mandát podložený vysokou volební účastí. Účast 68,92 procenta je nejvyšší od roku 1998, kdy byla mírně nad 74 procenty oprávněných voličů.\r\nNásleduje obsah vložený z jiného webu. Zde jej můžete přeskočit.\r\nPřejít před obsah vložený z jiného webu.\r\nParlamentní volby 2025\r\n\r\nVýsledky voleb s 34,51 % hlasů ovládlo hnutí ANO, za nimi SPOLU (23,36 %) a STAN (11,23 %). Volební účast byla 68,95 %. Živě jsme sledovali povolební vyjednávání, oba volební dny a sčítání komentoval náš volební štáb. Zkuste si sestavit vládu a zjistěte, co bude po volbách.\r\nVýsledky voleb 2025	Nově zvolení poslanci\r\n\r\nK volbám: Mapa obcí • Mapa měst • Mapa okrsků • Výsledky v zahraničí • Projekty redakce • ČR před volbami • Sněmovna • Výsledky minulých voleb\r\nVolby do Poslanecké sněmovny 2025', 'Poslanecká sněmovna zná nové složení. Bude v průměru o trochu mladší než ta dosavadní a bude v ní také více žen. Novinky přinášejí seznam všech nově zvolených zákonodárců.', 1, 'draft', NULL, '2025-10-05 20:21:12', '2025-10-05 20:21:12', NULL),
(10, 'Sentinel-6B je vybalen ', 'sentinel-6b-je-vybalen--1759695873', 'Po příjezdu do Kalifornie před pár týdny nastal čas, aby se inženýři pustili do předstartovních příprav nové družice pro sledování změn výšky mořské hladiny. Sentinel-6B z programu Copernicus má startovat v listopadu. Prvním krokem bylo opatrné vyjmutí této cenné družice z jejího přepravního kontejneru, aby mohly začít zevrubné zkoušky. Sentinel-6B má za úkol pokračovat v odkazu družice Sentinel-6 Michael Freilich, prvního zástupce řady Sentinel-6, který startoval v listopadu 2020. Mise Sentinel-6 slouží jako hlavní světová referenční mise radarových výškových měření. Cílem aktuálně chystané mise bude prodloužit nepřetržitou sérii měření výšky mořské hladiny, která začala už v 90. letech minulého století francouzsko-americkou družicí Topex-Poseidon, na kterou poté navázaly družicové mise z řady Jason.\r\nFungování radarového výškoměru Sentinelu-6.\r\nFungování radarového výškoměru Sentinelu-6.\r\nZdroj: https://www.esa.int/\r\n\r\nVzhledem k tomu, že vzestup hladiny moře je jednou z priorit globální agendy, řada organizací se zasadila o to, aby se mise Copernicus Sentinel-6 stala zlatým standardem pro rozšíření záznamů o měření výšky hladiny moře – a poskytovala data s dosud nevídanou přesností. Ačkoliv je Sentinel-6 součástí rodiny Copernicus od Evropské unie, jedná se o produkt výjimečné mezinárodní spolupráce. Do projektu se zapojila Evropská komise, ESA, NASA, EUMETSAT i NOAA s dodatečnou podporou francouzské kosmické agentury CNES. Kromě mapování výšky mořské hladiny pro pochopení dlouhodobých změn má Copernicus-6 také poskytovat data pro praktické provozní aplikace. Mise například měří výšku velkých vln, či rychlost větru. Tato data se používají pro předpovědi chování oceánu v téměř reálném čase. Družicové měření výšky hladiny poskytuje nejkomplexnější měření stavu oceánů, která jsou dnes k dispozici.\r\n\r\nDružice Sentinel-6 nesou výškoměr, který funguje tak, že měří čas, který radarový puls potřebuje k cestě od družice k povrchu Země a zase zpět k družici. Když se tato informace spojí s velmi přesnými údaji o pozici družice, přinesou údaje o výšce družice nad hladinou informace o výšce mořské hladiny. Palubní vybavení však také obsahuje pokročilý mikrovlnný radiometr, který dodala NASA. Vodní pára v atmosféře ovlivňuje rychlost šíření radarových pulsů, které využívá výškoměr. Ve výsledku tak má vodní pára vliv na údaje o výšce mořské hladiny. Pokročilý mikrovlnný radiometr zjistí množství vodní páry v atmosféře, aby bylo možné měření výškoměru korigovat a data byla přesná.\r\n\r\nPo plavbě z Německa do Texasu byla družice Sentinel-6 v srpnu přepravena po silnici do Kalifornie, na Vandenbergovu základnu, kde má NASA své zázemí. Právě tam byla družice dočasně uložena. Nyní mohla být přemístěna do areálu firmy Astrotech, kde byla vybalena a nyní probíhá její pečlivá inspekce. V průběhu následujících týdnů družice projde mnoha funkčními testy, kontrolami fotovoltaických panelů, nebo plněním nádrží. Družice bude následně uložena do aerodynamického krytu Falconu 9, který ji má v listopadu vynést na oběžnou dráhu. Přesné datum startu bude oznámeno v následujících týdnech.', 'Po příjezdu do Kalifornie před pár týdny nastal čas, aby se inženýři pustili do předstartovních příprav nové družice pro sledování změn výšky mořské hladiny. Sentinel-6B z programu Copernicus má startovat v listopadu. Prvním krokem bylo opatrné vyjmutí této cenné družice z jejího přepravního kontejneru, aby mohly začít zevrubné zkoušky. Sentinel-6B má za úkol pokračovat v odkazu družice Sentinel-6 Michael Freilich, prvního zástupce řady Sentinel-6, který startoval v listopadu 2020. Mise Sentinel-6 slouží jako hlavní světová ', 1, 'published', '2025-10-05 20:24:33', '2025-10-05 20:24:33', '2025-10-05 20:24:33', NULL),
(11, 'LVM-3 – CMS-02 (GSAT-7R)', 'lvm-3-cms-02-gsat-7r-1759695957', '\r\nPodrobnosti\r\n\r\nDatum:\r\n    16. října 2025 \r\nČas:\r\n    13:30\r\nŠtítky Akce:\r\n    ISRO, LVM-3\r\n\r\nMísto konání\r\n\r\n    Druhá startovní rampa, \r\n    Satish Dhawan Space Centre, India\r\n\r\n', ' Druhá startovní rampa, Satish Dhawan Space Centre,, India\r\n\r\nNáklad: Komunikační družice CMS-02\r\n', 1, 'published', '2025-10-05 20:25:57', '2025-10-05 20:25:57', '2025-10-05 20:25:57', NULL),
(12, 'Kosmotýdeník 681 (29. 9. – 5. 10.)', 'kosmotydenik-681-29-9-5-10-1759696078', '\r\nPřejít k obsahu\r\n\r\n    Seriály\r\n    Rubriky\r\n\r\nStarty\r\nPřednášky\r\nFórum\r\nOstatní\r\n\r\nSearch\r\n\r\nkřišťálová lupa\r\n\r\nsociální sítě\r\nYoutube\r\nFacebook\r\nInstagram\r\nX-twitter\r\nThreads\r\nSpotify\r\nIcon-bluesky-iconbluesky_logo\r\n\r\nPřímé přenosy\r\nNew Glenn (EscaPADE)\r\n55\r\nDNY\r\n:\r\n02\r\nHOD\r\n:\r\n32\r\nMIN\r\n:\r\n17\r\nSEK\r\nPřenos zde\r\n\r\n    New Glenn (EscaPADE)\r\n    30. listopadu 2025 0:00\r\n    Vulcan (SNC Demo-1)\r\n    31. prosince 2026 0:00\r\n\r\nkrátké zprávy\r\nTeraNet\r\nTomáš Pojezný\r\n3. října 2025 10:00	\r\n\r\nUniverzita Západní Austrálie (UWA) oznámila 2. října dokončení sítě TeraNet. Síť tvoří tři optické pozemní stanice v Západní Austrálii.\r\nHEO\r\nTomáš Pojezný\r\n3. října 2025 8:00	\r\n\r\nAustralská společnost HEO, která se zabývá pořizováním družicových snímků kosmických lodí na nízké oběžné dráze Země, se snaží rozšířit své snímkování pro monitorování vyšších oběžných drah.\r\nOrion\r\nTomáš Pojezný\r\n2. října 2025 15:00	\r\n\r\nDohoda o studiu soukromé mise astronautů s využitím kosmické lodi Orion je jedním z prvních kroků společnosti Lockheed Martin v jejím úsilí nabídnout kosmickou loď jako službu.\r\nU.S. Space Force\r\nTomáš Pojezný\r\n2. října 2025 13:00	\r\n\r\nOddělení pro zadávání veřejných zakázek amerických vesmírných sil vyhlásilo soutěž pro firmy na návrh kompaktního rádiofrekvenčního (RF) komunikačního terminálu, který by umožnil družicím přímé připojení k širokopásmové síti Starlink.\r\nNASA\r\nTomáš Pojezný\r\n2. října 2025 10:00	\r\n\r\nČínská národní kosmická agentura (CNSA) včera kontaktovala NASA ohledně možné srážky na oběžné dráze.\r\nProvozovatelé družic\r\nTomáš Pojezný\r\n2. října 2025 8:00	\r\n\r\nProvozovatelé družic žádají mezinárodního regulačního orgánu o pomoc s udržováním komunikačních linek mezi nimi a řešením potenciálních konjunkcí a dalších problémů s bezpečností vesmíru.\r\nExploration Company\r\nTomáš Pojezný\r\n2. října 2025 8:00	\r\n\r\nSpolečnost Exploration Company, evropský startup vyvíjející demonstrátor návratové kabiny Mission Possible, stále zkoumá, co se stalo v závěrečných fázích zkušebního letu před třemi měsíci, které vedly ke ztrátě lodi.\r\nVarda Space Industries\r\nTomáš Pojezný\r\n1. října 2025 15:00	\r\n\r\nSpolečnost Varda Space Industries, která se zabývá výzkumem mikrogravitace, podepsala dohodu s provozovatelem kosmodromu Southern Launch, která umožní až 20 návratů kapslí v Jižní Austrálii do roku 2028.\r\nRapidBeam\r\nTomáš Pojezný\r\n1. října 2025 13:00	\r\n\r\nUniverzita Jižní Austrálie 29. září oznámila dohodu s australským startupem RapidBeam a japonským startupem Warpspace o vývoji laserových komunikačních systémů.\r\n\r\nZobrazit všechny krátké zprávy »\r\n\r\nNaše podcasty\r\n\r\nDoporučujeme\r\n\r\nObjednejte si knihy našich autorů a nahlédněte tak do historie kosmonautiky.\r\n\r\nPoděkování\r\n\r\nNáš web běží spolehlivě díky perfektnímu servisu hostingu Blueboard.cz, děkujeme!\r\nKosmotýdeník 681 (29. 9. – 5. 10.)\r\n\r\n    Lukáš Houška\r\n\r\n5. října 2025	\r\nŽádné komentáře\r\n\r\n    Čas čtení: 10 min\r\n\r\nNová anténa	\r\n\r\nV NASA mají shutdown a financování agentury je nyní krom nejnutnějších projektů pozastaveno. Osud agentury je na vlásku a až další dny se uvidí, co se stane. V Kosmotýdeníku se tedy budeme věnovat těm, kteří mají co dělat. Například v Austrálii byla otevřena nová anténa pro komunikaci v hlubokém kosmu pro ESA. Podíváme se na ni blíže. Věnovat se však budeme i dvěma plánovaným startům. Jednak australské raketě Eris a pak hlavně druhému letu New Glennu. Společnost Blue Origin totiž chce při druhém startu úspěšně přistát s prvním stupněm, aby vůbec mohla uskutečnit start třetí. Podíváme se i na fotku Tianwen- 2 či zajímavý koncept Arc. Přeji vám dobré čtení a pěknou neděli.\r\n\r\nEvropa má novou anténu pro komunikaci v hlubokém kosmu\r\nPrvní a čtvrtá Deep space anténa (v Estrack) v australské Norcie\r\nPrvní a čtvrtá Deep space anténa (v Estrack) v australské Norcie\r\nZdroj: https://www.esa.int/\r\n\r\nStále více misí míří dále mimo oběžnou dráhu Země a zvyšují se i jejich nároky na přenosy dat k Zemi. Sítě, jako je třeba Deep Space Network, jsou kapacitně na hraně a tak je jakákoli další anténa vítaným pomocníkem. Jedna nová teď vznikla díky ESA a bude součástí systému Estrack.\r\n\r\nEvropská kosmická agentura tak nyní rozšířila své možnosti inaugurací nové antény pro hluboký vesmír. Parabola o průměru 35 m je čtvrtou takovou v síti Estrack, což je síť ESA pro sledování hlubokého kosmu. Anténa nazvaná „New Norcia 3“, která se nachází v New Norcia, asi 115 km severně od Perthu v Západní Austrálii, pomůže uspokojit rychle rostoucí potřeby agentury v oblasti stahování dat a zajistit vlastní nezávislost v této klíčové schopnosti. Zajímavostí je, že právě v Norcii byla v roce 2003 otevřena vůbec první anténa Estrack určená pro deep space komunikaci.\r\n\r\nGenerální ředitel ESA Josef Aschbacher na otevření, které proběhlo, 4. října uvedl: „Tato strategická investice posiluje komunikační schopnosti ESA v hlubokém vesmíru a maximalizuje návratnost nejcennějšího aktiva našich misí: dat dodávaných ze sond putujících daleko od Země. Otevírají se nové a vzrušující příležitosti mezi evropským a australským kosmickým sektorem, přičemž Austrálie tento týden oznámila mandát k zahájení jednání o dohodě o širší spolupráci s ESA.“\r\n\r\nVýstavba byla zahájená v roce 2021 a dokončená byla dle časového harmonogramu. Je výsledkem vzorové spolupráce mezi ESA a Austrálií, která má velký zájem být hostitelem této instituce. Až bude nová anténa pro hluboký vesmír v roce 2026 uvedena do plného provozu, bude podporovat současné vlajkové sondy ESA, které jsou součástí vědeckých, průzkumných a bezpečnostních flotil agentury, včetně sond Juice, Solar Orbiter, mezinárodní evropsko-japonské mise BepiColombo, Mars Expressu či Hera, a bude klíčovým nástrojem pro nadcházející mise, včetně Plato, Envision, Ariel, Ramses a Vigil.\r\nNová anténa v Norcie\r\nNová anténa v Norcie\r\nZdroj: https://www.esa.int/\r\n\r\nNové zařízení bude podporovat i mezinárodní mise. V rámci vzájemných dohod o vzájemné podpoře s partnery agentury může nová anténa podporovat další kosmické agentury, jako je NASA, japonská JAXA a indická ISRO, a také komerční kosmické mise, čímž se zvýší návratnost vědeckých poznatků a provozní efektivita pro všechny zúčastněné strany.\r\n\r\nČtvrtá anténa ESA pro hluboký vesmír, druhá na lokalitě New Norcia, je technologicky nejsofistikovanější anténou tohoto systému. Zahrnuje pokročilé možnosti komunikace v hlubokém kosmu, včetně komponent, které jsou chlazeny na teplotu okolo -226 °C. Tato citlivost umožňuje detekovat extrémně slabé signály ze vzdálených kosmických sond a maximalizovat datový tok. Pro přenos se bude používat 20kW radiofrekvenční zesilovač pro přenos povelů k sondám vzdálených miliony a dokonce miliardy kilometrů od Země. Anténa bude provádět uplink a downlink v pásmech X, K a Ka s výhledem rozšíření o pásmo X. Anténa je také vybavena pokročilými časovacími systémy a špičkovými radiofrekvenčními komunikačními nástroji a technikami pro podporu komunikace v hlubokém kosmu. Anténa je schopná přesného sledování rychlostí 1 stupeň za sekundu v azimutu i elevaci.\r\n\r\nStanice Estrack agentury ESA v New Norcia v Západní Austrálii byla otevřena v roce 2003 a demonstruje silné zapojení ESA v asijsko-pacifickém regionu a zejména v Austrálii. Jedná se o pokračující dlouhodobou spolupráci mezi ESA a Austrálií v oblasti kosmického výzkumu. Enrico Palermo, vedoucí Australské kosmické agentury (ASA), uvedl: „Austrálie je dobře známá jako důvěryhodný, zkušený a schopný operátor v oblasti komunikace v hlubokém kosmu. Tato investice ESA a australské vlády uvolní miliony dolarů v místní ekonomické hodnotě a také zaměstnanosti během předpokládané životnosti určené na 50 let. Je to další kapitola v příběhu australského a evropského partnerství v kosmickém průmyslu, které budeme dále rozvíjet, jakmile začneme vyjednávat novou dohodu o spolupráci mezi Austrálií a ESA,“ dodal.\r\n\r\nOdhadované náklady na výstavbu nové antény činí 62,3 milionu eur, které zahrnují pořízení a výstavbu antény a modernizaci infrastruktury a budovy, s příspěvkem 3 milionů eur od Australské kosmické agentury, který byl vyčleněn na vývoj nové stanice na New Norcia. Výstavbu vedl evropský průmysl, přičemž společnosti Thales Alenia Space (Francie) a Schwartz Hautmont Construcciones Metálicas (Španělsko) byly spolupracujícími hlavními dodavateli. Významná část rozpočtu byla vynaložena v Austrálii za účasti několika australských společností, včetně TIAM Solutions, Thales Australia, Fredon a Westforce Construction.\r\n\r\nPřed několika dny, v rámci závěrečné kalibrace, nová anténa úspěšně přijala svůj první signál ze sondy ESA Euclid. New Norcia nabízí strategickou geografickou polohu, která umožňuje nepřetržité pokrytí a je perfektním doplňkem stanic ESA v Malargüe (Argentina) a Cebreros (Španělsko). Jakmile bude celé nové zařízení zprovozněno, stane se New Norcia první pozemní stanicí ESA vybavenou dvěma anténami pro hluboký vesmír. Západní Austrálie je také místem, nad kterým se náklad vypouštěný z evropského kosmodromu v Kourou ve Francouzské Guyaně odděluje od své nosné rakety. Několik set metrů od antén pro hluboký vesmír proto sleduje menší a obratnější 4,5metrová anténa rakety Vega-C a Ariane 6 a získává kritické telemetrické údaje používané k monitorování stavu těchto nosných raket za letu.\r\n\r\nKosmický přehled týdne:\r\n\r\nNa Mezinárodním astronautickém kongresu, který se konal 3. října, vystoupil také Adam Gilmour, spoluzakladatel a generální ředitel společnosti Gilmour Space. Jeho společnost se letos pokusila o první start australské rakety určené k letu na oběžnou dráhu. Let jejich první rakety však trval jen pár sekund. Nicméně Glimour by celkem optimistický. „Jsme s tím docela spokojeni,“ řekl o krátkém letu, kdy raketa letěla 14 sekund, a motory běžely 23 sekund. „Samozřejmě jsme z něj získali spoustu dat, spoustu informací.“ Společnost stále vyšetřuje příčinu selhání. „Vypadá to, že to, co se při startu pokazilo, je něco, co jsme nikdy předtím netestovali dostatečně blízko podmínkám startu,“ řekl, ale nespecifikoval, o co šlo. Zmínil také, že raketu ovlivňuje i blízkost u moře, vzduch je zde vlhký a to podporuje korozi. Exemplář jejich rakety byl na kosmodromu řadu měsíců. Její pobyt byl prodloužen kvůli problémům se schvalovacími procesy, což si firma bere na svá bedra. Příště si proto na vše dají větší pozor a počítají s tím, že další pokus o start rakety Eris by mohl proběhnout příští rok. Potvrdil také, že firma má zatím dostatek finančních zdrojů na další pokus.\r\nFotografie z prvního startu rakety Eris v Austrálii\r\nFotografie z prvního startu rakety Eris v Austrálii\r\nZdroj: https://pbs.twimg.com/\r\n\r\nBlue Origin čeká perný závěr roku. Jejich raketa New Glenn s potenciálně vícenásobně použitelným prvním stupněm se chystá na druhý start. První dopadl dobře, až na to, že první stupeň se během návratu rozpadl. Nebyl by to žádný závažný problém, kdyby už při druhém startu neplánovala společnost úspěšné přistání za účelem použití tohoto stupně pro vyslání landeru na Měsíc. Každopádně nyní se připravuje start s dvěma sondami EscaPADE (od NASA) určených k letu k Marsu a průzkumu jeho atmosféry. Pat Remias, viceprezident společnosti Blue Origin pro vývoj kosmických systémů, ve čtvrtek uvedl, že si společnost je jistá, že přistání při druhém letu rakety New Glenn proběhne bez problémů. „Plně hodláme při příštím startu zachránit první stupeň rakety New Glenn,“ řekl Remias v prezentaci na Mezinárodním astronautickém kongresu v Sydney. „Tento první stupeň použijeme při dalším startu New Glennu,“ řekl Remias. „To je záměr. Tentokrát jsme si docela jisti. Věděli jsme, že při prvním startu bude šance malá, ale nyní je velká.“ Nejdříve lednu roku 2026 pak má být pomocí tohoto stupně vyslán velký lunární lander Blue Moon. Připomeňme, že SpaceX potřebovala 20 startů Falconu 9 a více než pět let, než se jí podařilo první přistání stupně. Trvalo dalších 15 měsíců, než SpaceX poprvé vypustila již jednou použitý první stupeň. V Blue Origin plánují, že pokud se jim s prvním stupněm podaří přistát, připraví jej ke startu za 90 dní. Společnost je k tomuto agresivnímu plánu nucena i svými pravidly. Cílem je vyrobit menší počet prvních stupňů a pak je pravidelně používat. Takže zatímco výroba druhých stupňů je celkem svižná a ten určený pro druhý let prodělal i statický zážeh, první stupeň pro druhý let se ještě kompletuje. V minulém týdnu bylo uvedeno, že montáž motorů BE-4 je v „plném proudu“.\r\n\r\nPřehled z Kosmoanutixu:\r\n\r\nK ucelení přehledu o tom, co se vše se během týdne stalo v kosmonautice, přidáváme seznam všech vyšlých článků na Kosmonautixu. Začali jsme oznámením, že ESA vybrala novou misi pro studium Země, která ponese pojmenování WIVERN. I toto pondělí vám Aleš Svoboda přinesl souhrn toho, jak probíhá jeho astronautický výcvik. Další pěkná zpráva z Evropy přišla, když ESA udělila italskému výrobci raket, firmě Avio, kontrakt za 40 milionů eur na návrh demonstrátoru znovupoužitelného stupně orbitální rakety. Spolupráce firem Prusa Research a TRL Space vedla ke vzniku polykarbonátového filamentu pro stolní 3D tiskárny, který by měl najít využití i v kosmonautice. Firmě Firefly se tento týden opět nedařilo. Při testech explodoval první stupeň rakety Alpha, který byl určen k sedmému letu. Ohlédli jsme se také za aktuálním stavem příprav na pilotovanou misi Artemis II. Vyšel také nový díl seriálu Vesmírné výzvy, který vám shrnul kosmonautické dění v září. Díky datům nasbíraných během desetiletí evropskou družicí CryoSat mohli výzkumníci identifikovat 85 dříve neznámých jezer v Antarktidě. Amerika nemá schválený rozpočet na další fiskální rok a NASA a další americké vládní agentury jsou v takzvaném shutdownu. Vědci procházející bohaté archivy dat pořízené sondou Cassini objevili nové komplexní organické molekuly, které do svého okolí vyvrhuje saturnův měsíc Enceladus. Jedná se o jasný důkaz, že jeho v podpovrchovém oceánu dochází ke komplexním reakcím. ESA připravila video, které vás vezme na působivý průlet nad kroutícími se kanály, které vyhloubila voda, ostrovy, které odolaly erozi a bludištěm kopcovitého terénu. Řeč je samozřejmě o povrchu Marsu. Nová geostacionární telekomunikační družice ViaSat-3 Flight 2 od společnosti Viasat dorazila 30. září v časných ranních hodinách na Floridu. Rumunský Národní institut pro letecko-kosmický výzkum ‘Elie Carafoli’ dokončil kvalifikační testy sestupového a přistávacího testovacího modelu Space Rideru.\r\n\r\nSnímek týdne:\r\n\r\nČína zveřejnila snímek sondy Tianwen-2, který byl pořízen krátce po startu, ale zveřejněn až nyní. Vzhledem k tomu, že takových fotek je jak šafránu, zaslouží si pozornost. Fotka byla pořízena pomocí robotického ramene, které nám poskytuje hezký pohled na sondu a návratový modul pro doručení vzorků z blízkozemního asteroidu 469219 Kamoʻoalewa, kam má Tianwen-2 namířeno. Sonda je v kosmu už 125 dní a nachází se 45 milionů km od Kamoʻoalewy a zhruba 43 milionů kilometrů od Země.\r\nSonda Tianewen-2 při odletu od Země\r\nSonda Tianewen-2 při odletu od Země\r\nZdroj: https://pbs.twimg.com/\r\n\r\nVideo týdne:\r\n\r\nFirmou Inversion byl tímto videem představen stroj Arc, který má být schopen rychle (prý za hodinu) doručit náklad na jakékoli místo na Zemi pomocí malého kosmického vztlakového tělesa. Arc umí pomocí klapek řídit průlet atmosférou a následně přesně přistát pomocí řiditelného padáku. Ve videu idylicky přistává na pláži. Zařízení má poskytovat různě velké nákladové prostory. Na oběžné dráze mají být celé konstelace, které v případě potřeby zamíří na volané místo přistání. Nosič? Jakýkoli, zařízení má být schopno použít velkou škálu komerčních nosičů. Firma cílí nejen na komerční zákazníky, ale nabízí operační schopnosti potřebné v armádě.\r\n\r\nZdroje informací:\r\nhttps://www.esa.int/\r\nhttps://www.esa.int/\r\nhttps://en.wikipedia.org/\r\nhttps://spacenews.com/\r\n\r\nZdroje obrázků:\r\nhttps://www.esa.int/ESA_Multimedia/Images/2025/09/ESA_s_fourth_deep_space_antenna_in_New_Norcia_Australia\r\nhttps://www.esa.int/ESA_Multimedia/Images/2025/09/ESA_s_fourth_deep_space_antenna_in_New_Norcia_Australia\r\nhttps://www.esa.int/ESA_Multimedia/Images/2025/10/Inauguration_of_ESA_s_fourth_deep_space_antenna\r\nhttps://www.esa.int/ESA_Multimedia/Images/2025/09/ESA_s_first_and_fourth_deep_space_antennas\r\nhttps://pbs.twimg.com/media/G2KLdUMboAEdIIr?format=jpg&name=large\r\nhttps://pbs.twimg.com/media/GxEJnUZbAAARmxP?format=jpg&name=medium\r\n\r\nRubrika:\r\nAktuální dění	\r\n\r\nŠtítky:\r\nArc, Austrálie, Blue Origin, Eris-1, ESA, ESTRACK, Inversion Space, Kosmotýdeník, New Glenn, Norcia	\r\nPočet zobrazení: 439\r\n\r\nHodnocení:\r\n\r\n5 / 5. Počet hlasů: 2\r\n\r\nSdílejte tento článek:\r\nHlášení chyb a nepřesností\r\n\r\nDalší podobné články:\r\nSentinel-6B je vybalen\r\n\r\n    Dušan Majer\r\n    5 října, 2025	\r\n\r\nRumunský institut schválil prototyp Space Rideru pro shozové testy\r\n\r\n    Dušan Majer\r\n    4 října, 2025	\r\n\r\nProlétněte se nad marsovskou oblastí Xanthe Terra\r\n\r\n    Dušan Majer\r\n    3 října, 2025	\r\n\r\nVýtrysky na Enceladu	\r\nCassini potvrdila komplexní chemické reakce v oceánu na Enceladu\r\n\r\n    Dušan Majer\r\n    3 října, 2025	\r\n\r\nPrevPředchozíRumunský institut schválil prototyp Space Rideru pro shozové testy\r\nDalšíSentinel-6B je vybalen\r\nNext\r\n\r\nKomentáře:\r\nOdběr komentářů\r\nPřihlášení\r\nPro přidání komentáře se prosím přihlaste\r\n0 Komentáře\r\n© 2012 – 2025\r\n\r\nKontakty\r\nTechnická podpora\r\n\r\nVesmírné výzvy\r\nVesmírná technika\r\nVesmírné zprávy\r\nŽivě a česky\r\nČesky otitulkovaná videa\r\n\r\nRSS kanál – všechny příspěvky\r\nRSS kanál – bez krátkých zpráv\r\nRSS kanál – komentáře\r\n', 'V NASA mají shutdown a financování agentury je nyní krom nejnutnějších projektů pozastaveno. Osud agentury je na vlásku a až další dny se uvidí, co se stane. V Kosmotýdeníku se tedy budeme věnovat těm, kteří mají co dělat. Například v Austrálii byla otevřena nová anténa pro komunikaci v hlubokém kosmu pro ESA. Podíváme se na ní blíže. Věnovat se však budeme i dvěma plánovaným startům. Jednak australské rakety Eris a pak hlavně druhému letu New Glennu. ', 1, 'published', '2025-10-05 20:27:58', '2025-10-05 20:27:58', '2025-10-05 20:27:58', NULL),
(13, 'Firma Avio získala kontrakt od ESA na demonstrátor znovupoužitelného horního stupně', 'firma-avio-ziskala-kontrakt-od-esa-na-demonstrator-znovupouzitelneho-horniho-stupne-1759696188', '\r\n\r\n Tato úvodní fáze měla být zaměřena na „identifikaci potřeb v oblasti technologického zrání pro demonstraci opakovaně použitelného horního stupně“. Agentura sice neuvedla žádné informace o firmách, které se do této 1. fáze zapojily, ale vypadá to, že tato etapa trvala zhruba 12 měsíců a vyústila ve zveřejnění výzvy agentury ESA na předkládání návrhů pro Fázi 2, k čemuž došlo  v květnu 2025. Podle výzvy by tato druhá fáze zahrnovala aktivity „až do kritického milníku návrhu“.\r\n\r\n29. září ESA oznámila, že v rámci Fáze 2 udělila firmě Avio kontrakt ve výši 40 milionů Euro na vývoj projektu. Během následujících 24 měsíců má italská firma dokončit souhrn požadavků na „systémy demonstrační mise a technologická řešení. Vše zakončí předběžný design jak pro letový hardware, tak i pro pozemní systémy.“ Zdá se, že tento rozsah prací těsně nedosahuje kritického milníku návrhu, jak bylo uvedeno v původní výzvě.\r\n\r\n„Využíváme pokroku dosaženého v oblasti pokročilých technologií pro pohonné systémy využívající kapalné pohonné látky, návrat do atmosféry, zpětnou přepravu a opětovnou použitelnost, čímž doplňujeme probíhající úsilí o snížení rizik demonstrací opakovaně použitelných spodních stupňů a podporujeme různé možné scénáře, včetně vývoje rodiny raket Vega, jakož i dalších nově definovaných plně opakovaně použitelných nosných systémů v Evropě,“ uvedl hlavní technický poradce ESA pro kosmickou dopravu Giorgio Tumino.\r\n\r\nAčkoli bylo o návrhu společnosti Avio zveřejněno jen málo podrobností, doprovodná grafika (úvodní obrázek tohoto článku) znázorňuje první stupeň rakety motor P120C na tuhé pohonné látky, který slouží jako první stupeň rakety Vega C. Pokud bude na prvním stupni skutečně použit P120C a horní stupeň je v grafice vyobrazen v měřítku, hotový demonstrátor bude pravděpodobně vysoký přibližně 36,5 metru.\r\n\r\nZnovupoužitelný horní stupeň zobrazený na grafice se z hlediska vzhledu podobá fázi lodi Starship společnosti SpaceX, což naznačuje motorické přistávání. Ve své tiskové zprávě z 29. září společnost Avio uvedla, že pro tento projekt využije své odborné znalosti s pohonnými systémy na bázi kapalných pohonných látek, kam patří i směs kapalného kyslíku s metanem. To pravděpodobně naznačuje, že stupeň bude poháněn řadou raketových motorů MR10 vyráběných firmou Avio pro horní stupeň jejich budoucí rakety Vega E.\r\n\r\nKromě bohatých zkušeností s kapalnými pohonnými látkami zmiňuje tisková zpráva firmy Avio, že projekt využije také „znalosti získané prostřednictvím programu návratového stroje Space Rider.“ Společnost Avio se však na této stránce programu Space Rider nepodílí. Je hlavním dodavatelem servisního modulu, který vyvíjí na platformě horního stupně rakety Vega C. Servisní modul se však při návratu do atmosféry zničí. Práce na návratovém modulu Space Rider vede společnost Thales Alenia Space s přispěním společností Beyond Gravity, CIRA, SENER, GMV, Frentech, SABCA, ArianeGroup a CIMSA.\r\n\r\nPřeloženo z:\r\nhttps://europeanspaceflight.com/\r\n\r\nZdroje obrázků:\r\nhttps://europeanspaceflight.com/wp-content/uploads/2025/09/Avio-Wins-ESA-Contract-to-Develop-Reusable-Rocket-Upper-Stage.webp\r\n', 'Evropská kosmická agentura udělila italskému výrobci raket, firmě Avio, kontrakt za 40 milionů Euro na návrh demonstrátoru znovupoužitelného stupně orbitální rakety. ESA v březnu 2024 nejprve vydala výzvu na první fázi vývoje znovupoužitelného horního stupně.', 1, 'published', '2025-10-05 20:29:48', '2025-10-05 20:29:48', '2025-10-05 20:29:48', NULL),
(14, 'Cassini potvrdila komplexní chemické reakce v oceánu na Enceladu', 'cassini-potvrdila-komplexni-chemicke-reakce-v-oceanu-na-enceladu-1759696270', 'V roce 2005 sonda Cassini odhalila první důkazy o tom, že Enceladus disponuje oceánem skrytým pod ledovým povrchem. Výtrysky vody z prasklin v okolí jižního pólu měsíce tam vyvrhují do okolního prostoru zrnka ledu menší než běžná zrnka písku ze Země. Některé z těchto maličkých kousků dopadnou zpět na povrch měsíce, zatímco ostatní uniknou a vytváří kolem Saturnu prstenec, který kopíruje dráhu Enceladu.\r\n\r\nHlavní autor nové studie Nozair Khawaja nejprve vysvětluje, co jsme už věděli: „Cassini detekovala vzorky z Enceladu pokaždé, když prolétávala přes Saturnův prstenec E. Už dříve jsme v těchto ledových zrnkách našli mnoho organických molekul včetně prekurzorů pro aminokyseliny.“ Tato ledová zrnka mohou být i stovky let stará. S tím, jak stárnou, mohou podléhat erozi a být pozměněna intenzivním kosmickým zářením. Vědci chtěli prozkoumat čerstvá zrnka, která byla vyvržena teprve nedávno, aby získali lepší přehled o tom, co se přesně odehrává v oceánu na Enceladu.\r\n\r\nNaštěstí už odborníci měli potřebná data. V roce 2008 Cassini prolétla přímo skrz výtrysky ledových částeček. Nedotčená zrnka, která byla vyvržena pouze před pár minutami, narazila do palubního přístroje CDA (Cosmic Dust Analyzer) pro analýzu prachových částic rychlostí 18 km/s.\r\nPrstenec E Saturnu je tvořen ledovými zrnky vyvrženými z Enceladu, který je viditelný uprostřed tohoto snímku.\r\nPrstenec E Saturnu je tvořen ledovými zrnky vyvrženými z Enceladu, který je viditelný uprostřed tohoto snímku.\r\nZdroj: https://www.esa.int/\r\n\r\nNešlo pouze o nejčerstvější zrnka, jaká kdy Cassini detekovala, ale také o nejrychlejší. A jak vysvětluje Nozair Khawaja, na rychlosti záleželo: „Tato ledová zrnka neobsahují jen zmrzlou vodu, ale i další molekuly včetně organických. Při menších rychlostech se led roztříští a signály ze shluků vodních molekul mohou zakrýt signály určitých organických látek. Jenže když ledová zrnka zasáhnou CDA rychle, nedojde ke shluku vodních molekul a my máme šanci vidět tyto (dříve skryté) signály.“\r\nUmělecká představa sondy Cassini se Saturnem (není v měřítku), s vyznačeným analyzátorem kosmického prachu. Zobrazené prachové zrno není skutečným zástupcem toho, co CDA detekoval. Pro ilustraci je zobrazena meziplanetární prachová částice, pravděpodobně pocházející z komety nebo planetky, zachycená v zemské atmosféře.\r\nUmělecká představa sondy Cassini se Saturnem (není v měřítku), s vyznačeným analyzátorem kosmického prachu. Zobrazené prachové zrno není skutečným zástupcem toho, co CDA detekoval. Pro ilustraci je zobrazena meziplanetární prachová částice, pravděpodobně pocházející z komety nebo planetky, zachycená v zemské atmosféře.\r\nZdroj: https://www.esa.int/\r\n\r\nTrvalo několik let, než se podařilo složit poznatky z předešlých průletů a aplikovat je na dešifrování těchto dat. Ovšem nyní Nozair Khawaja a jeho tým odhalili nový druh molekul, který byl přítomen uvnitř čerstvých ledových zrnek. Všimli si, že určité molekuly, které byly už dříve objeveny v prstenci E, se také nacházejí v čerstvých ledových zrnkách. To potvrdilo, že vznikají v oceánu Enceladu. Objevili také úplně nové molekuly, které nebyly nikdy dříve pozorovány na ledových zrnkách z Enceladu. Pro chemiky, kteří čtou tento článek, nově detekované molekulární fragmenty zahrnovaly alifatické, (hetero)cyklické estery/alkeny, ethery/ethyl a předběžně také sloučeniny obsahující dusík a kyslík.\r\n\r\nNa Zemi jsou tyto stejné molekuly zapojeny do řetězců chemických reakcí, které ve výsledku vedou ke komplexnějším molekulám, které jsou nezbytné pro život. „Existuje mnoho různých cest od organických molekul, která jsme objevili v datech z Cassini k potenciálně biologicky relevantním sloučeninám, což posiluje pravděpodobnost, že by tento měsíc mohl hostit život,“ uvádí Nozair Khawaja a dodává: „V datech, která momentálně prozkoumáváme, je toho mnohem více, takže se už těšíme na to, co v blízké budoucnosti objevíme.“\r\n\r\nSpoluautor zmíněného vědeckého článku, Frank Postberg, dodává: „Molekuly, které jsme našli v čerstvě vyvrženém materiálu potvrzují, že organické molekuly, které Cassini detekovala v Saturnově prstenci E, nejsou jen produktem jejich dlouhého vystavení kosmickému prostředí, ale byly už dostupné v oceánu Enceladu.“ Nicolas Altobelli, vědec agentury ESA zapojený do mise Cassini, doplňuje: „Je fantastické vidět, jak se z dat, která Cassini nasbírala před téměř dvěma dekádami, vynořují nové objevy. Skutečně to ukazuje dlouhodobý impakt našich kosmických misí. Těším se na porovnání dat z Cassini s daty z dalších misí ESA, které mají navštívit ledové měsíce Saturnu i Jupiteru.“\r\n\r\nObjevy z mise sondy Cassini jsou cenné pro plánování budoucí mise ESA, která bude vyhrazena průzkumu Enceladu. Studijní fáze přípravy této ambiciózní mise již začala. Plánem je prolétnout skrz výtrysky a dokonce přistát na povrchu u jižního pólu měsíce za účelem odběru vzorků. Tým vědců a inženýrů již zvažuje výběr moderních vědeckých přístrojů, které by taková sonda mohla nést. Nejnovější objevy, na kterých se podílel přístroj CDA, mohou pomoci s tímto rozhodováním.\r\n\r\nEnceladus splňuje všechny požadavky na to, aby byl obyvatleným prostředím, které může podporovat život. Je zde přítomná kapalná voda, zdroj energie, specifický soubor chemických prvků a komplexní organické molekuly. Mise, která by provedla měření přímo z povrchu měsíce a hledala známky života, by Evropě zajistila přední místo ve vědě o Sluneční soustavě. „Dokonce i neobjevení života na Enceladu by byl obrovský objev, protože by to vyvolalo vážné otázky, proč není život přítomen v takovém prostředí, když jsou tam správné podmínky,“ doplnil Nozair Khawaja.\r\nVýbor planetárních vědců označil Saturnův měsíc Enceladus za nejzajímavější cíl pro příští „velkou“ vesmírnou vědeckou misi ESA, která naváže na mise Juice, LISA a NewAthena (nejnovější velké mise ESA). Žádná vesmírná agentura dosud na malém Enceladu nepřistála. Přesto má tento měsíc obrovský potenciál pro nové vědecké objevy, zejména v oblasti obyvatelnosti. Gejzíry, které tryskají z jeho ledové kůry, jsou bohaté na organické sloučeniny, z nichž některé jsou klíčové pro život. Oceán také zdá se skrývá silný zdroj chemické energie, která by mohla být palivem pro živé organismy. Dopad takové mise by mohl být obrovský. Evropě by to opět zajistilo jedinečné místo v popředí vědy o Sluneční soustavě.\r\nVýbor planetárních vědců označil Saturnův měsíc Enceladus za nejzajímavější cíl pro příští „velkou“ vesmírnou vědeckou misi ESA, která naváže na mise Juice, LISA a NewAthena (nejnovější velké mise ESA). Žádná vesmírná agentura dosud na malém Enceladu nepřistála. Přesto má tento měsíc obrovský potenciál pro nové vědecké objevy, zejména v oblasti obyvatelnosti. Gejzíry, které tryskají z jeho ledové kůry, jsou bohaté na organické sloučeniny, z nichž některé jsou klíčové pro život. Oceán také zdá se skrývá silný zdroj chemické energie, která by mohla být palivem pro živé organismy. Dopad takové mise by mohl být obrovský. Evropě by to opět zajistilo jedinečné místo v popředí vědy o Sluneční soustavě.\r\nZdroj: https://www.esa.int/\r\n\r\nPřeloženo z:\r\nhttps://www.esa.int/\r\n\r\nZdroje obrázků:\r\nhttps://www.esa.int/…/enceladus_jets_and_shadows/17568049-1-eng-GB/Enceladus_jets_and_shadows.jpg\r\nhttps://www.esa.int/…/saturn_s_moon_enceladus/26893565-1-eng-GB/Saturn_s_moon_Enceladus.jpg\r\nhttps://www.esa.int/…/enceladus_orbiting_within_saturn_s_e_ring/26887533-1-eng-GB/Enceladus_orbiting_within_Saturn_s_E_ring.jpg\r\nhttps://www.esa.int/…/enceladus_mission_concept/26236661-1-eng-GB/Enceladus_mission_concept.jpg', 'Vědci procházející bohaté archivy dat pořízené sondou Cassini objevili nové komplexní organické molekuly, které do svého okolí vyvrhuje saturnův měsíc Enceladus. Jedná se o jasný důkaz, že v podpovrchovém oceánu dochází ke komplexním reakcím. Některé z nich by mohly být součástí řetězce, který vede k ještě komplexnějším, potenciálně biologicky relevantním, molekulám. Objev, který byl 1. září publikován v časopise Nature Astronomy, dále posiluje význam vyhrazené mise Evropské kosmické agentury, která by nejprve kroužila kolem Enceladu a poté by na něm i přistála.', 1, 'published', '2025-10-05 20:31:10', '2025-10-05 20:31:10', '2025-10-05 20:31:10', NULL),
(15, 'V ČR vznikl materiál pro 3D tisk vhodný pro kosmický prostor ', 'v-cr-vznikl-material-pro-3d-tisk-vhodny-pro-kosmicky-prostor--1759696400', 'Materiál s obchodním označením Prusament PC Space Grade Black se chlubí vlastnostmi, které jsou vhodné pro použití na družicích, ale uplatnění najde i v laboratořích částicové fyziky, ale i jinde, kde uživatel vyžaduje rozměrovou přesnost, odolnost a elektrostatickou bezpečnost. Další výhodou je jeho nízká cena i to, že se s ním dá pracovat na běžných domácích 3D tiskárnách. Právě díky těmto vlastnostem by mohl otevřít cestu k rychlejšímu, dostupnějšímu a bezpečnějšímu vývoji součástek pro družice, výzkumná zařízení i náročné průmyslové aplikace.\r\nMateriál Prusament PC Space Grade Black se vyznačuje rozměrovou stálostí, odolává kosmickému prostředí, má velmi nízké hodnoty odplyňování a přitom se dá tisknout na běžných stolních 3D tiskárnách.\r\nMateriál Prusament PC Space Grade Black se vyznačuje rozměrovou stálostí, odolává kosmickému prostředí, má velmi nízké hodnoty odplyňování a přitom se dá tisknout na běžných stolních 3D tiskárnách.\r\nZdroj: Tisková zpráva PRUSA Research a TRL Space\r\n\r\n„Jsme přesvědčeni, že se jedná o přelomový materiál, který dokáže zpřístupnit vývoj kosmických technologií mnohem širšímu okruhu vývojářů, výzkumníků i firem. To, co bylo dosud doménou specializovaných laboratoří s drahým vybavením, je teď možné tisknout jednoduše a levně na běžné 3D tiskárně. A právě v této kombinaci špičkových parametrů a dostupnosti vidíme skutečný posun,“ uvedl Josef Průša, zakladatel a CEO společnosti Prusa Research, která si vzhledem k unikátním vlastnostem nového materiálu podala patentovou přihlášku a v současnosti čeká na její schválení. Právě díky snadné tisknutelnosti na komerčně široce dostupných stolních tiskárnách bez nutnosti investovat do speciálního vybavení se výrazně zjednodušuje a zrychluje vývoj potřebných dílů. Výrobci i výzkumníci si nyní mohou potřebnou součástku navrhnout a vytisknout přímo ve své laboratoři nebo firmě během několika hodin bez nutnosti zadávat výrobu externím dodavatelům, čekat na doručení nebo platit vysoké částky za zakázkovou produkci. To urychluje celý inovační cyklus od prvního nápadu až po funkční prototyp nebo finální díl.\r\n\r\n„Aditivní výroba má v kosmickém průmyslu obrovský potenciál. Umožňuje rychlejší vývoj, prototypování i výrobu strukturálních dílů, a to za zlomek ceny běžných řešení. Co ale dlouho chybělo, byl spolehlivý, dostupný a snadno tisknutelný materiál s vhodnými vlastnostmi pro použití ve vesmíru. Spoluprací s Prusa Research jsme tento problém vyřešili,“ říká Petr Kapoun, CEO společnosti TRL Space, která materiál plánuje využívat v rámci svých vlastních projektů a přispět tak k vyšší flexibilitě a efektivitě při vývoji a testování kosmických zařízení.\r\n\r\nFilament Prusament PC Space Grade Black je navržen tak, aby splňoval náročné požadavky na použití v prostředí, kde přijde do kontaktu s vakuem, prudkým kolísáním teplot a budou na něj kladeny vysoké nároky na elektrostatickou bezpečnost. Jednou z největších výzev při použití plastových materiálů v kosmickém prostoru je takzvané odplyňování, tedy uvolňování mikroskopických zbytků látek, které z materiálu ve vakuu sublimují. Tyto výpary mohou například znečistit optické povrchy, poškodit citlivou elektroniku nebo narušit nejrůznější měření. Prusament PC Spacem Grade Black má v tomto ohledu mimořádně dobré výsledky. Podle dosavadních laboratorních testů vykazuje jen minimální ztrátu hmotnosti (v parametru TML hluboko pod limitem Evropské kosmické agentury) a v testu CVCM, který měří množství kondenzovatelných výparů, dokonce dosáhl nulové hodnoty. To je výsledek, kterého běžné a cenově dostupné plasty obvykle nedosahují. Materiál je navíc elektrostaticky disipativní (ESD safe), což znamená, že dokáže bezpečně odvádět elektrostatický náboj. To je klíčové především v případech, kdy přichází do kontaktu s elektronikou.\r\nVývojáři již pracují na tom, aby materiál splnil i ty nejpřísnější standardy potřebné pro výrobu nosných konstrukcí malých CubeSatů.\r\nVývojáři již pracují na tom, aby materiál splnil i ty nejpřísnější standardy potřebné pro výrobu nosných konstrukcí malých CubeSatů.\r\nZdroj: Tisková zpráva PRUSA Research a TRL Space\r\n\r\nI přesto, že lze filament tisknout na běžné stolní 3D tiskárně – tedy na zařízení, které se dnes běžně používá třeba ve školách, dílnách nebo startupech, zachovává si velmi dobrou přesnost a spolehlivost i při tisku větších objektů, například o velikosti 20 × 20 centimetrů, což je rozměr, který by u méně kvalitního materiálu mohl způsobit deformace. Díky tomu se hodí třeba pro výrobu krytů elektroniky, držáků kabelů nebo jiných menších technických dílů. Vývojáři již pracují na tom, aby materiál splnil i ty nejpřísnější standardy potřebné pro výrobu nosných konstrukcí malých CubeSatů. A v plánu jsou i další testy, například měření odolnosti vůči radiaci, které proběhne v laboratořích CERNu, nebo zkoušky při opakovaném zahřívání a ochlazování v podmínkách simulujících kosmické prostředí, které provede Evropská kosmická agentura. Právě tyto extrémní výkyvy teplot a radiace jsou běžnou zátěží, které musejí komponenty družic a dalších zařízení ve vesmíru odolávat.\r\n\r\nZdroje informací:\r\nTisková zpráva PRUSA Research a TRL Space\r\n\r\nZdroje obrázků:\r\nTisková zpráva PRUSA Research a TRL Space', 'Spolupráce firem Prusa Research a TRL Space vedla ke vzniku polykarbonátového filamentu pro stolní 3D tiskárny, který nyní vstupuje na trh. ', 1, 'published', '2025-10-05 20:33:20', '2025-10-05 20:33:20', '2025-10-05 20:33:20', NULL),
(16, 'Zářijová Kosmoschůzka 2025', 'zarijova-kosmoschuzka-2025-1759696502', 'středa 24. září 2025 od 17:30 do 20:00 hodin,\r\nÚstav letadlové techniky\r\nČVUT FS,\r\nKarlovo náměstí 13\r\n121 35 Praha 2\r\n\r\nvstup zdarma\r\n\r\n\r\nzobrazit mapu\r\n\r\n\r\nPřednášky kosmoschůzky:\r\n\r\nPremiéra Eris – Jan Baštecký\r\nStarship Flight 10 – Jiří Myška\r\n\r\n \r\nProgram záříjové Kosmoschůzky\r\nProgram záříjové Kosmoschůzky\r\nzdroj: kosmo.cz\r\n\r\n \r\n\r\nZměna programu vyhrazena\r\nVíce informací na webových stránkách Kosmo Klubu, o.s. (http://klub.kosmo.cz/novinky) nebo na e-mailu Kosmoschůzek (kosmoschuzky@kosmo.cz). Hlavními organizátory akce jsou Petr Tomek – petrtomek98(c)gmail.com, Martin Kostera a Michal Václavík.\r\nPřidejte se k události také na Facebooku!\r\n\r\nZdroje obrázků:\r\nhttp://mek.kosmo.cz/cz/kk/kklogoc.jpg\r\nhttps://www.syfy.com/sites/syfy/files/styles/1200×680/public/2019/07/screen-shot-2019-07-09-at-3.15.31-pm.png\r\nhttp://klub.kosmo.cz/system/files/Kosmoschuzka202509.png', 'Blíží se poslední středa v měsíci. Ta zářijová vychází na 24. 09. 2025. Tentokrát opět do Ústavu letadlové techniky, ČVUT FS, Karlovo náměstí 13 (viz mapa níže) jsou zváni všichni příznivci kosmonautiky a příbuzných oborů. Kosmoschůzka nabídne dva atraktivní přednášející: Jan Baštecký shrne premiéru rakety Eris a dále Jiří Myška přinese informace o letu Starship Flight 10. Neváhejte a přijďte navštívit tuto akci, kterou pořádá Kosmo Klub z.s. Akce začíná v 17:30.', 1, 'published', '2025-10-05 20:35:02', '2025-10-05 20:35:02', '2025-10-09 11:54:41', NULL),
(17, 'Kosmické Brno | 12 – Frentech Aerospace / Podvozky SpaceRideru ', 'kosmicke-brno-12-frentech-aerospace-podvozky-spacerideru--1759696616', 'Věděli jste, že se v Brně staví družice, ale i jiný kosmický hardware? Sídlí tu celá řada firem, které si už stihly vybudovat velmi dobré jméno nejen na českém, ale i celoevropském poli. V pořadu Kosmické Brno, který vyrábí Hvězdárna a planetárium Brno, si budeme ukazovat, na jakých úžasných kosmických projektech tyto firmy zrovna pracují. Tento díl byl natočen v září roku 2025.\r\n\r\n\r\nZdroje obrázků:\r\nhvezdarna.cz\r\n\r\nRubrika:\r\nAktuální dění, Foto a video, Technologie	\r\n\r\nŠtítky:\r\nBrno, Frentech Aerospace, Hvězdárna a planetárium Brno, Kosmické Brno, SpaceRider	\r\n', 'Evropa by se už za pár let mohla dočkat znovupoužitelné kosmické lodě, která může provádět na oběžné dráze experimenty, či vynášet družice. Poté se SpaceRider vrátí na Zemi a připraví na další misi. Na tomto ambiciózním projektu se podílejí také firmy z České republiky. Velkou roli při vývoji podvozků tohoto miniraketoplánu sehrála firma Frentech Aerospace z Brna. Věděli jste, že se v Brně staví družice, ale i jiný kosmický hardware? Sídlí tu celá řada firem, které si už stihly ', 1, 'published', '2025-10-05 20:36:56', '2025-10-05 20:36:56', '2025-10-05 20:36:56', NULL),
(18, 'Cassini potvrdila komplexní chemické reakce v oceánu na Enceladu', 'cassini-potvrdila-komplexni-chemicke-reakce-v-oceanu-na-enceladu-1760013613', 'fbfbfbbfgxwdggw', 'hfvbfbfbfggbgf', 1, 'draft', NULL, '2025-10-09 12:40:13', '2025-10-09 12:40:13', NULL),
(19, 'vcxbcvbv ,. ,nb nvb', 'vcxbcvbv-nb-nvb-1760018852', ' b nvb b vbv b bn  v v v vn', 'cvnbc v cv cv vbncghncgnc cvc v', 1, 'published', '2025-10-09 14:07:32', '2025-10-09 14:07:32', '2025-10-09 14:07:32', NULL),
(20, 'vcxbcvbv ,. ,nb nvbnnn', 'vcxbcvbv-nb-nvbnnn-1760040641', 'vb nhbm,jmnn,b,n,mn', '12345grccxbm xcv.nvbxmnb xc,v', 1, 'draft', NULL, '2025-10-09 20:10:41', '2025-10-09 20:10:41', NULL);
INSERT INTO `articles` (`id`, `title`, `slug`, `content`, `excerpt`, `author_id`, `status`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(21, 'khuzjgfr', 'khuzjgfr-1760043920', ' fzjfzujf fzzkfzjmvmbi ,v  m v mf v f f tg', 'hjgjmnhmvmgmguuuuuuuuuuuuuuuuuuuuuuuuuuuu  fzu ufu ufjuf uf ', 1, 'draft', NULL, '2025-10-09 21:05:20', '2025-10-09 21:05:20', NULL),
(22, 'Sentinel-6B je vybalen  hh', 'sentinel-6b-je-vybalen-hh-1760044121', 'gfgfghfggh', 'dfhgfgthfgtgfcggncfgchgh', 1, 'published', '2025-10-09 21:08:41', '2025-10-09 21:08:41', '2025-11-13 09:52:40', NULL),
(23, 'o dobré vodě', 'o-dobre-vode-1761050460', 'Stáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletu', 'Stáhněte si Firefox do svého telefonu a tabletu', 1, 'draft', NULL, '2025-10-21 12:41:00', '2025-10-21 12:41:00', NULL),
(24, 'o vodě dobré', 'o-vode-dobre-1761050608', 'Stáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletu', 'Stáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletuStáhněte si Firefox do svého telefonu a tabletu', 1, 'published', '2025-10-21 12:43:28', '2025-10-21 12:43:28', '2025-10-21 12:43:28', NULL),
(25, 'Jak na to že bbb', 'jak-na-to-ze-bbb-1761588466', 'Na cokoliv přece :) jojo jjj', 'na co to nnn', 1, 'draft', NULL, '2025-10-22 11:30:41', '2025-10-27 18:07:46', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `article_categories`
--

CREATE TABLE `article_categories` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `article_categories`
--

INSERT INTO `article_categories` (`id`, `article_id`, `category_id`, `created_at`) VALUES
(1, 13, 1, '2025-10-09 12:36:10'),
(2, 9, 2, '2025-10-09 12:36:10'),
(3, 2, 14, '2025-10-09 12:36:10'),
(4, 19, 1, '2025-10-09 14:07:32'),
(5, 20, 8, '2025-10-09 20:10:41'),
(6, 21, 5, '2025-10-09 21:05:20'),
(7, 22, 1, '2025-10-09 21:08:41'),
(8, 23, 10, '2025-10-21 12:41:00'),
(9, 24, 11, '2025-10-21 12:43:28'),
(12, 25, 10, '2025-10-27 18:07:46');

-- --------------------------------------------------------

--
-- Struktura tabulky `article_images`
--

CREATE TABLE `article_images` (
  `article_id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `created_at`, `deleted_at`) VALUES
(1, 'Nezařazeno', 'nezarazeno', 'Kategorie pro články bez zařazení', NULL, '2025-10-09 10:08:03', NULL),
(2, 'Technologie', 'technologie', 'Články o technologiích', NULL, '2025-09-11 19:41:52', NULL),
(4, 'vesmír', 'vesmir', 'blabla o vesmíru', NULL, '2025-10-09 07:39:22', NULL),
(5, 'galerie života', 'galerie-zivota', 'obrázky, básníčky...', 3, '2025-10-09 08:33:07', NULL),
(6, 'o ničem;', 'o-nicem', 'o všem možném', 3, '2025-10-09 08:46:25', NULL),
(8, 'Novinky', 'novinky', 'Aktuální novinky a události', NULL, '2025-09-11 19:41:52', NULL),
(10, 'dobrá voda', 'dobra-voda', 'o dobré vodě', 2, '2025-10-21 12:37:03', NULL),
(11, 'nejlepší voda', 'nejlepsi-voda', 'o nejlepší vodě', 10, '2025-10-21 12:37:40', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `galleries`
--

CREATE TABLE `galleries` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `featured_image_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `galleries`
--

INSERT INTO `galleries` (`id`, `parent_id`, `name`, `slug`, `description`, `featured_image_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 5, 'květinky', 'kvetinky', 'obrázky kvetoucích květinek a podobně', NULL, '2025-10-27 23:11:39', '2025-10-28 00:58:47', NULL),
(4, 5, 'pamlsky', 'pamlsky', 'žrádýlko hhh', NULL, '2025-10-27 23:30:37', '2025-11-13 13:55:29', NULL),
(5, NULL, 'květinkynn', 'kvetinkynn', 'květiny', NULL, '2025-10-27 23:52:17', '2025-10-30 18:12:22', NULL),
(6, NULL, 'lopaty', 'lopaty', 'no přece ty nástroje na nabírání sypkých hmot', NULL, '2025-10-30 18:10:42', '2025-11-14 10:24:28', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `gallery_images`
--

CREATE TABLE `gallery_images` (
  `gallery_id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `gallery_images`
--

INSERT INTO `gallery_images` (`gallery_id`, `image_id`) VALUES
(1, 1),
(4, 5),
(5, 2),
(5, 5),
(6, 4);

-- --------------------------------------------------------

--
-- Struktura tabulky `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `thumb_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_size` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `images`
--

INSERT INTO `images` (`id`, `file_path`, `thumb_path`, `original_name`, `title`, `description`, `file_size`, `width`, `height`, `mime_type`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'img_6903a01897ec11.53350443.jpg', 'img_6903a01897ec11.53350443_thumb.jpg', 'IMG_20220224_125941_984.jpg', 'malý', 'jhzžtgvv', 262562, 842, 1268, 'image/jpeg', '2025-10-30 18:27:52', '2025-11-13 11:22:34', NULL),
(2, 'img_6903a14852f0c2.96978798.jpg', 'img_6903a14852f0c2.96978798_thumb.jpg', 'IMG_20220224_130244_362.jpg', 'vbvvvcv', 'vcvfcvffnjf', 4045729, 2572, 4133, 'image/jpeg', '2025-10-30 18:32:56', '2025-10-31 21:06:59', NULL),
(4, 'img_6916fe9a5b2a59.71622705.JPG', 'img_6916fe9a5b2a59.71622705_thumb.JPG', 'IMG_9041.JPG', '', '', 6893572, 6000, 4000, 'image/jpeg', '2025-11-14 11:04:11', '2025-11-14 11:04:11', NULL),
(5, 'img_6916feb1507028.08156346.JPG', 'img_6916feb1507028.08156346_thumb.JPG', 'IMG_9044.JPG', 'narozeniny máji', 'jde sfouknout dort', 9322680, 6000, 4000, 'image/jpeg', '2025-11-14 11:04:34', '2025-11-14 11:14:48', NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `username`, `ip_address`, `attempted_at`) VALUES
(1, 'admin', '127.0.0.1', '2025-11-13 10:49:57'),
(2, 'admin', '127.0.0.1', '2025-11-13 10:50:45');

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'author',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `role`, `active`, `created_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$i5QZMebyfORru1U1uNxvKexuEo1Ym/izESiB71s3.lBcgCEHdd0QS', 'admin@example.com', 'admin', 1, '2025-09-11 19:41:52', NULL),
(2, 'editor', '$2y$10$i5QZMebyfORru1U1uNxvKexuEo1Ym/izESiB71s3.lBcgCEHdd0QS', 'editor@example.com', 'editor', 1, '2025-09-11 19:41:52', NULL),
(3, 'author', '$2y$10$i5QZMebyfORru1U1uNxvKexuEo1Ym/izESiB71s3.lBcgCEHdd0QS', 'author@example.com', 'author', 1, '2025-09-11 19:41:52', NULL);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_articles_slug` (`slug`),
  ADD KEY `idx_articles_status` (`status`),
  ADD KEY `idx_articles_author` (`author_id`),
  ADD KEY `idx_articles_published` (`published_at`);

--
-- Indexy pro tabulku `article_categories`
--
ALTER TABLE `article_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_article_category` (`article_id`,`category_id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexy pro tabulku `article_images`
--
ALTER TABLE `article_images`
  ADD PRIMARY KEY (`article_id`,`image_id`),
  ADD KEY `image_id` (`image_id`);

--
-- Indexy pro tabulku `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categories_slug` (`slug`);

--
-- Indexy pro tabulku `galleries`
--
ALTER TABLE `galleries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_galleries_parent` (`parent_id`),
  ADD KEY `idx_galleries_featured_image` (`featured_image_id`);

--
-- Indexy pro tabulku `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`gallery_id`,`image_id`),
  ADD KEY `image_id` (`image_id`);

--
-- Indexy pro tabulku `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username_time` (`username`,`attempted_at`);

--
-- Indexy pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pro tabulku `article_categories`
--
ALTER TABLE `article_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pro tabulku `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pro tabulku `galleries`
--
ALTER TABLE `galleries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pro tabulku `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `article_images`
--
ALTER TABLE `article_images`
  ADD CONSTRAINT `article_images_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `galleries`
--
ALTER TABLE `galleries`
  ADD CONSTRAINT `fk_galleries_featured_image` FOREIGN KEY (`featured_image_id`) REFERENCES `images` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_galleries_parent` FOREIGN KEY (`parent_id`) REFERENCES `galleries` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD CONSTRAINT `gallery_images_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gallery_images_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE;
COMMIT;
