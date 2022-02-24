package me.thenshow

import java.io.File
import scala.io.Source

abstract class BlogFile {
    def render(): String
}

case class Pdf(f:File) extends BlogFile {
    def render(): String = {
        "<div style=\"text-align:center\"><h4>Pdf viewer testing</h4><iframe src=\"" +
        s"https://docs.google.com/viewer?url=${f.getPath()}&embedded=true\" frameborder=\"0\" height=\"500px\" width=\"100%\"></iframe></div>"
    }
}

case class Tex(f:File) extends  BlogFile {
    def render(): String = {
        if ( !("mkdir ./tmp" !) && 
             !("echo -e \"n\" | pdflatex -output-directory=./tmp -output-format=pdf " + f.getPath !)
         ) {
            val fileName = f.getName().split(".tex")[0] + ".pdf"
            val mv_ret = "mv ./tmp/" + fileName + " ./" + fileName !
            val rm_ret = "rm -rf ./tmp" !

            val pdfFile = new File("./"+fileName)
            assert( mv_ret == 0 && pdfFile.exists && pdfFile.canRead )
            val pdf = new Pdf(pdfFile)
            pdf.render()
        } else {
            s"<p>There was an error opening TeX file ${ f.getPath() }</p>"
        }
        
    }
}

case class ASCII(f:File) extends BlogFile {
    def render(): String = {
        "<p>" +  Source.fromFile(f.getPath()).getLines.mkString + "</p>"   
    }
}

case class Image(f:File) extends BlogFile {
    def render(): String = {
        "<img src=\"" + f.getPath() + "\">"               
    }
}
case class Unknown(f:File) extends BlogFile {
    def render(): String = {
        "<p>Unknown file: " + f.getPath() + "</p>"
    }
}