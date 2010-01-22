<?php

include "lib/setup.php";

$response = array();

if (getCurrentUser()) {

  if (ereg ("^[0-9]+$", $_POST["article_pmid"], $regs)) $article_pmid = $regs[0];
  else $article_pmid = 0;

  if (ereg ("^[0-9]+$", $_POST["genome_id"], $regs)) $genome_id = $regs[0];
  else $genome_id = 0;

  if (ereg ("^[0-9]+$", $_POST["disease_id"], $regs)) $disease_id = $regs[0];
  else $disease_id = 0;

  if (ereg ("^[0-9]+$", $_POST["variant_id"], $regs)) $variant_id = $regs[0];
  else if (aa_sane($_POST["variant_aa_change"])) {
    if (ereg ("^([^0-9]+)([0-9]+)([^0-9]+)$",
	      aa_long_form ($_POST["variant_aa_change"]),
	      $regs)) {
      $aa_from = $regs[1];
      $aa_pos = $regs[2];
      $aa_to = $regs[3];
    }
    $gene = strtoupper ($_POST["variant_gene"]);
    $variant_id = evidence_get_variant_id ($gene,
					   $aa_pos, $aa_from, $aa_to,
					   true);
    $edit_id = evidence_get_latest_edit ($variant_id, 0, 0, 0, true);
    $response["latest_edit_v${variant_id}a0g0"] = $edit_id;
    $response["latest_edit_id"] = $edit_id;
    $response["variant_id"] = $variant_id;
    $response["please_reload"] = true;
    $response["variant_key"] = "$gene ".aa_short_form("$aa_from$aa_pos$aa_to");
  }
  else {
    die ("Invalid variant specified");
  }

  if ($article_pmid || $genome_id || $disease_id) {
    $latest_edit_id = evidence_get_latest_edit
	($variant_id, $article_pmid, $genome_id, $disease_id, true);
    $response["latest_edit_v${variant_id}a${article_pmid}g${genome_id}"] = $latest_edit_id;
    $response["latest_edit_id"] = $latest_edit_id;
    $response["html"] = evidence_render_row
      (theDb()->getRow ("SELECT * FROM edits WHERE edit_id=?",
			array($latest_edit_id)));
    ereg ("id=\"([^\"]+)", $response["html"], $regs);
    $response["e_id"] = $regs[1];
  }

  header ("Content-type: application/json");
  print json_encode ($response);

}

?>
