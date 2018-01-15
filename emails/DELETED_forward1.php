<?php
exit();
?>
							<div class="panel panel-default">
									
								<div class="panel-body">
					
								<h1>Add new forwarder</h1>
								<form name="SingleForwarder" method="post" action="DoAddSingleForwarder.php" class="form-horizontal">
									

										<div class="form-group">
											<label class="col-sm-2 control-label">
												Email Address:
											</label>										
											<div class="col-sm-4">
												<span class="input-icon">								
												<table border="0">
												<tr>
												<td>
												<input name="LocalPart" type="text" placeholder="Local Part" value="<?php print $LocalPart; ?>" id="form-field-11" class="form-control">
												</td>
												<td>
												<b>@</b>
												</td>
												<td>
											<select name="DomainID" class="form-control" id="email">
										
												<option value="-1">Select Domain</option>				
								
												<?php
												$oDomain = new Domain();
												$oDomain->GetDomainList($DomainArray, $ArrayCount, $ClientID, $Role);
												for($x = 0; $x < $ArrayCount; $x++)
												{
													print "<option value=\"".$DomainArray[$x]["id"]."\"";

													if($DomainID == $DomainArray[$x]["id"])
													{
														print " selected ";
													}

													print ">".$DomainArray[$x]["domain_name"]."</option>";
												}
												?>
											</select>	
												</td>
												</tr>
												</table>	
												</span>
											</div>
										</div>
										
								
										<div class="form-group">
											<label class="col-sm-2 control-label">
												Forward To:
											</label>
											<div class="col-sm-4">
												<span class="input-icon">
												<input name="ForwardTo" type="text" id="form-field-11" class="form-control">
												<i class="fa fa-envelope-o"></i>
												</span>										
											</div>
										</div>
									
										<div class="form-group">

											<div class="col-sm-4">
												<input type="submit" value="Add new single forwarder" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateSingleForwarder(); return false;">
													<span class="ladda-spinner"></span>
													<span class="ladda-progress" style="width: 0px;"></span>
												</input>
											</div>
										</div>

								</form>

								</div>
							</div>


