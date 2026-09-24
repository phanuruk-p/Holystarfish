<?php
declare(strict_types=1);

/** Shared email-safe branding: table layout, inline styles, and an attached logo. */
function emailBrandHeader(string $baseUrl,string $label): string {
    ob_start(); ?>
<tr><td height="5" bgcolor="#b28a4b" style="height:5px;line-height:5px;font-size:0">&nbsp;</td></tr>
<tr><td align="center" bgcolor="#f5efe5" style="padding:24px 24px 18px;background:#f5efe5;border-bottom:1px solid #dfcfb7">
<a href="<?= e($baseUrl.'/index.php') ?>" style="text-decoration:none"><img src="<?= e($baseUrl.'/assets/logo-holystarfish.jpg') ?>" width="176" height="176" alt="Holystarfish — เครื่องประดับที่เปล่งประกายดั่งท้องทะเล" style="display:block;width:176px;height:176px;max-width:100%;border:0;border-radius:8px;color:#795b2f;font:18px Georgia,serif"></a>
<p style="margin:18px 0 0;color:#795b2f;font-family:Tahoma,Arial,sans-serif;font-size:12px;letter-spacing:2px;line-height:1.6"><?= e($label) ?></p>
</td></tr>
<?php return ob_get_clean();
}
function emailBrandFooter(string $note): string {
    ob_start(); ?>
<tr><td align="center" bgcolor="#234f55" style="padding:28px 24px;background:#234f55;border-top:3px solid #b28a4b;color:#fff8e9">
<p style="font:italic 23px Georgia,serif;line-height:1.5;margin:0;color:#f3dfb7">Inspired by the Sea.<br>Crafted to Shine.</p>
<p style="font:16px Georgia,serif;letter-spacing:1px;margin:16px 0 8px;color:#fff8e9">Holystarfish</p>
<p style="font-family:Tahoma,Arial,sans-serif;font-size:12px;line-height:1.7;margin:0;color:#e4eeeb"><?= e($note) ?></p>
</td></tr>
<?php return ob_get_clean();
}
