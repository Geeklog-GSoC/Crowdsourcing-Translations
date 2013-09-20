<?php include_once '../../../lib-common.php'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
  <title>Geeklog Documentation - Geeklog CrowdTranslator plugin</title>
  <link rel="stylesheet" type="text/css" href="../docstyle.css" title="Dev Stylesheet">
  <meta name="robots" content="noindex">
</head>

<body>
  <p><a href="http://www.geeklog.net/" style="background:transparent"><img src="../images/newlogo.gif" alt="Geeklog" width="243" height="90"></a></p>
  <div class="menu"><?php echo "<a href='{$_CONF['site_url']}/docs/english/index.html'>Geeklog Documentation</a>" ?> - Geeklog CrowdTranslator plugin
  </div>


  <div dir="ltr" style="text-align: left;" trbidi="on">
    <h2 style="text-align: left;">
      Geeklog CrowdTranslator plugin</h2>
      <h3>Introduction</h3>
      <div>
        <div>
          CrowdTranslator is a plugin that allows "crowdsourcing" the translation of Geeklog, i.e. once installed, it allows users to contribute translations of Geeklog's user interface texts for other languages.
        </div>

        <div>
          This is a being developed by <a href="http://www.linkedin.com/profile/view?id=188717601">Benjamin Talic</a>&nbsp;under the mentorship of <a href="http://www.linkedin.com/profile/view?id=11473251">Dirk Haun</a>&nbsp;as a project during the <a href="https://www.google-melange.com/gsoc/homepage/google/gsoc2013">Google Summer of Code 2013</a>.
          <br />
          <br />
          The plugin uses a side block to provide a form for translation submitting, it will add badges to user profiles, the public page is intended for users to manage their translations, the admin page will allow overall translations management.
          <br />
          <br />
          <h3>What are the plugins features?</h3>
          <div style="text-align: left;">
          </div>
          <ul style="text-align: left;">
            <li>Translations submission</li>
            <li>Gamification badges</li>
            <li>Translations management and Simple translation queries</li>
            <li>Remote submission of translations</li>
            <li>Managing local and remote users</li>
            <li>Translation packing</li>
          </ul>


          <h3>Translations submission</h3>
          <br />
          <div>
            <a href="../docs_pictures/form.png" imageanchor="1" style="clear: right; float: right; margin-bottom: 1em; margin-left: 1em;"><img border="0" height="400" src="http://3.bp.blogspot.com/-qfkrLTO3KIU/UjthyFkLVLI/AAAAAAAACRQ/tJ9r_lSOI-k/s400/form.png" width="130" /></a>The translations are submitted via a form on the left side block. The left side is used because left side blocks are available on more pages than the right side blocks.
          </div>
          <div>
            <br />
            The form provides users with the possibility to select or change the selected language. Users can type in any language they want but a list of languages is generated from the database of previously translated and languages Geeklog ships with as a auto suggest for users while typing in. This allows users to create new languages but should prevent creating duplicate entries.
            <br />
            <br />
            The form provides buttons for highlighting phrases on the page, or removing the highlight. To keep everything as small as possible the form shows a limited number of phrases at once but the users can scroll up and down.
            <br />
            If a certain phrase already has a submitted translation the translation is shown and users have the possibility to vote those translations up or down. After a certain number of negative votes the translation will be deleted.
            <br />
            <br />
            <a href="http://1.bp.blogspot.com/-yD4R7lHnvv0/UjtkNek8xUI/AAAAAAAACRs/OV694vDCeuw/s1600/badges_notification..png" imageanchor="1" style="clear: left; display: inline !important; margin-bottom: 1em; margin-right: 1em; text-align: center;"><img border="0" src="http://1.bp.blogspot.com/-yD4R7lHnvv0/UjtkNek8xUI/AAAAAAAACRs/OV694vDCeuw/s1600/badges_notification..png" /></a>
            <br />
            <br />
            The form also provides users with guidelines on how to use the translator.
            <br />
            Finally if the user earns a badge a notification will be shown.
            <br />
            <br />
            <h3>Gamification</h3>
            <div>
              Currently the plugin supports 4 types of badges, 2 of which are continuous. The continuous badges will grow in level once a user has reached the necessary goal.
            </div>
            <div>
              <a href="../docs_pictures/badge2.png" imageanchor="1" style="clear: left; float: left; margin-bottom: 1em; margin-right: 1em;"><img border="0" height="200" src="../docs_pictures/badge2.png" width="144" /></a><a href="../docs_pictures/badge1.png" imageanchor="1" style="clear: left; display: inline !important; margin-bottom: 1em; margin-right: 1em; text-align: center;"><img border="0" height="200" src="../docs_pictures/badge1.png" width="144" /></a><a href="../docs_pictures/badge3.png" imageanchor="1" style="margin-left: 1em; margin-right: 1em;"><img border="0" height="200" src="../docs_pictures/badge3.png" width="140" /></a><a href="../docs_pictures/badge4.png" imageanchor="1" style="clear: right; display: inline !important; margin-bottom: 1em; margin-left: 1em;"><img border="0" height="200" src="../docs_pictures/badge4.png" width="140" /></a>
            </div>
          </div>
        </div>
      </div>
      <div>
        <br />
      </div>
      <div>
        Additional badges might be added. If you do have requests for badges look up the code documentation, or the authors contact information or Geeklog's developer mailing list.
      </div>
      <div>
        <br />
      </div>
      <h3>Translations management</h3>
      <div>
        Translations management is divided into to two "levels" the global,admin management and the single user management. The single user management is available in the CrowdTranslator public page and allows users to manage their own translations. The global translations management is available through the CrowdTranslator admin panel. Visually and functionally the two are very similar.
      </div>
      <div>
        <br />
      </div>
      <div class="separator" style="clear: both; text-align: center;">
        <a href="../docs_pictures/translations_table.png" imageanchor="1" style="margin-left: 1em; margin-right: 1em;"><img border="0" height="195" src="../docs_pictures/translations_table.png" width="640" /></a>
      </div>
      <div class="separator" style="clear: both; text-align: left;">
        The table not only provides you with a preview of translated phrases but also lets you:
      </div>

      <ol style="text-align: left;">
        <li>Block users/sites** from submitting translations*</li>
        <li>Delete translations</li>
        <li>Query by:</li>
        <ol>
          <li>User or Site**</li>
          <li>Language</li>
          <li>Votes</li>
          <li>Time posted</li>
        </ol>
      </ol>
      <div>
        *<span style="color: red;">Note: </span>Blocking a user will also delete their translations
      </div>
      <div>
        **Site refers to websites which you have allowed to submit translations to your website
      </div>
      <div>
        <br />
      </div>
      <h3>Remote submission of translations</h3>
      <br />
      <div>
        The plugin allows communication between several instances of Geeklog. That is a collection of translations from one site can be transferred to another. In order to achieve this the sender must get approved from the receiver. In fact the only way (currently) is to contact the site admin and request an account. To access this part of the plugin click the 'Manage Remote Submission' from the admin panel. Setting up remote senders is easy enough, as is sending data.
      </div>
      <div>
        <br />
      </div>
      <h4>Allowing remote submission</h4>
      <div>
        <a href="../docs_pictures/peers.png" imageanchor="1" style="clear: right; float: right; margin-bottom: 1em; margin-left: 1em;"><img border="0" height="168" src="../docs_pictures/peers.png" width="320" /></a>By simply imputing a new site name and password you have allowed a new user(remote site) to submit translations to your database. After that give the credentials to the remote site's Admin and you are ready to go.&nbsp;
      </div>
      <div>
        Previously submitted translations are not accepted.
      </div>
      <div>
        Only translations with more than 1 up vote will be sent.
      </div>
      <div>
        <br />
      </div>
      <h4>Sending translations</h4>
      <div class="separator" style="clear: both; text-align: center;">
        <a href="../docs_pictures/submit_remote.png" imageanchor="1" style="clear: right; float: right; margin-bottom: 1em; margin-left: 1em;"><img border="0" height="170" src="../docs_pictures/submit_remote.png" width="320" /></a>
      </div>
      <div>
        to send translations it is necessary to specify the website you are sending to, if this website is <i>www.geeklog.net</i> you would enter <i>geeklog.net.&nbsp;</i>Other than that you will need to provide the site name and credentials the remote site admin created for you and the language you are sending. (the list of languages is generated from the entries in you database)
      </div>
      <div>
        <br />
      </div>
      <h4>Bragging rights</h4>
      <div>
        A "new" feature is the iframe you can add to your website, if you have submitted translations to a remote website you will get code which can be included in your site. For now it will simply display the number of translations you have submitted to the remote site. In the future instead of this the badges available to local users should be displayed.
      </div>
      <div>
        <br />
      </div>
      <h3>Managing local and remote users</h3>
      <div>
        Most user management is done inside the translations table.(see&nbsp;<b>Translations management </b>section of this document). The difference between sites and users is that users can simply be unblocked from the admin panel, sites however are permanently deleted, to allow a site to submit translations again you have to create a new account for them
      </div>
      <div>
        <br />
      </div>
      <h3>Packing translations</h3>
      <div>
        To pack translations you simply have to click the text <i>pack this</i>&nbsp;in the admin panel. This will create and output a PHP file inside the language folder. It will take translations from your website and generate a file with the same structure Geeklog uses.
      </div>
      <div>
        <br />
      </div>
      <div>
        <br />
      </div>
      <h3>More information can be found</h3>
      <div>
        <ul style="text-align: left;">
          <li><a href="http://summergeeek.blogspot.com/">The development blog</a></li>
          <li><a href="http://wiki.geeklog.net/index.php/Crowdsourcing_Translations">The wiki page</a></li>
        </ul>
      </div>
    </div>

    <div class="footer">
      <a href="http://wiki.geeklog.net/">The Geeklog Documentation Project</a><br>
      All trademarks and copyrights on this page are owned by their respective owners. Geeklog is copyleft.
    </div>

  </body>
  </html>
