<?php
	// Display Form
	function form_mode(&$object) {
		global $modx;
		$output_form = NULL;
		//----  Allow post?
		if ($object->canPost) {
			// Render Form
			$tpl = new CChunkie($object->templates["form"]);
			$tpl->AddVar('jot',$object->config);
			$tpl->AddVar('form',$object->form);
			$object->config["html"]["form"] = $tpl->Render();
			$object->config["html"]["form"] = preg_replace('~\[\+(.*?)\+\]~s', '', $object->config["html"]["form"]);
			
			//onSetFormOutput event
			if (null !== ($output = $object->doEvent("onSetFormOutput"))) return $output;
			
			$output_form = $object->config["html"]["form"];
			
		} // -----
		// Output or placeholder?
		if ($object->config["output"]) return $output_form;
	}
?>
