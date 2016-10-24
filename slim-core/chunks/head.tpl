<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MODXRenderer for Slim</title>
    <base href="[[++site_url]]">

    <!-- Open Graph Tags -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="[[++site_name]]" />
    <meta property="og:description" content="[[++site_tagline]]" />
    <meta property="og:url" content="[[++request_uri]]" />
    <meta property="og:site_name" content="[[++site_name]]" />
    <meta name="twitter:site" content="[[++social.twitter]]" />
    <meta name="description" content="[[++site_tagline]]" />
    <link rel="icon" type="image/x-icon" href="favicon.ico" sizes="16x16 24x24 32x32 48x48" />
    <link rel="apple-touch-icon-precomposed" href="apple-touch-icon.png" />
    [[- MODX comment
    <link href="//fonts.googleapis.com/css?family=Lato:200,300,400" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    ]]
    <link rel="stylesheet" href="[[++assets_url]]css/reset.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            width: 100vw;
            height: 100vh;
            font-family: "Lato", Helvetica, Arial, sans-serif;
            color: #FFF;
            background: #010000;
            font-size: 16px;
        }
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: 200;
        }
        h1 {
            color: [[++site_css.sepia]];
            font-family: 'Lato', sans-serif;
            font-size: 3rem;
        }

        a {
            color: [[++site_css.sepia]];
            text-decoration: none;
            [[++site_css.transitions]]
        }

        a:hover {
            color: [[++site_css.blue]];
            [[++site_css.transitions]]
        }

        img {
            max-width: 100%;
            height: auto;
        }

        main {
            display: flex;
            flex-wrap: wrap;
            -webkit-tap-highlight-color: rgba(255, 255, 255, 0);
            -moz-transition: -moz-filter 0.5s ease, -webkit-filter 0.5s ease, -ms-filter 0.5s ease, -moz-filter 0.5s ease;
            -webkit-transition: -moz-filter 0.5s ease, -webkit-filter 0.5s ease, -ms-filter 0.5s ease, -webkit-filter 0.5s ease;
            -ms-transition: -moz-filter 0.5s ease, -webkit-filter 0.5s ease, -ms-filter 0.5s ease, -ms-filter 0.5s ease;
            transition: -moz-filter 0.5s ease, -webkit-filter 0.5s ease, -ms-filter 0.5s ease, filter 0.5s ease;
            margin-top: 4rem;
        }

        nav {
            position: fixed;
            height: 4rem;
            z-index: 1;
            width: 100vw;
            padding: 1rem;
            background: rgba(0, 0, 0, .8);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: #FFF;
            font-weight: 200;
            text-transform: uppercase;
        }

        nav a:hover,
        nav a:hover .grey,
        nav a:hover .gray,
        nav a:hover span {
            color: [[++site_css.sepia]];
            [[++site_css.transitions]]
        }

        nav .fa {
            color: #AAA;
            font-size: 1.5rem;
            margin: 0.25rem;
        }

        nav .fa:hover {
            color: [[++site_css.sepia]];
            [[++site_css.transitions]]
        }

    </style>
    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "Website",
            "name": "MODXRenderer for Slim",
            "url": "[[++site_url]]",
            "image": "[[++site_url]]apple-touch-icon.png"
            "author": {
                "@type": "Person",
                "name": "Yee Jee Tso"
            },
            "copyrightHolder": {
                "@type": "Person",
                "name": "Yee Jee Tso"
            }
        }
    </script>
</head>
