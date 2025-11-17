<?php $str_out .='
            <div class="col-sm-6" >
                <div class="col-sm-12">
                    <div class="col-sm-5 BoxRowLabel" style="text-align:center">
                     DOCUMENTAZIONE 
                    </div> 
                    <div class="col-sm-4 BoxRowLabel">
                         Carica tutta la cartella
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <select name="AllFolder" class="form-control" style="width:6rem;">
                            <option value="N">NO
                            <option value="Y">SI
                        </select>
                    </div>
                    
                    
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRow" style="width:100%;height:10rem;">
                    <div class="example">
                        <div id="fileTreeDemo_1" class="col-sm-12 BoxRowLabel" style="height:10rem;overflow:auto"></div>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem;">
                    <div class="imgWrapper" id="preview_img" style="height:60rem;overflow:auto; display: none;">
                        <img id="preview" class="iZoom"  />
                    </div>
                    <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>                
                </div>	
                <div class="clean_row HSpace4"></div>				
                <div class="col-sm-12 BoxRow">
                    <div class="col-sm-2 BoxRowLabel">
                        Documento
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <input type="hidden" name="Documentation" id="Documentation" value="">
                        <span id="span_Documentation" style="height:6rem;width:40rem;font-size:1.1rem;"></span>
                    </div>
                </div>					
                <div class="col-sm-12 BoxRow">    

                </div>					
            </div>';