package me.thenshow

import java.io.File
import scala.io.Source
import sys.process._
import scala.util.Properties

abstract class BlogFile {
    def render(): String
}

case class Pdf(f:File) extends BlogFile {
    def render(): String = {
        
        "<div style=\"text-align:center\"><h4>Pdf viewer testing</h4><iframe src=\"" +
        "https://docs.google.com/viewer?url=" + 
        Properties.envOrElse("PHPHOST", "dev.thenshow.me/blog") + "/" + f.getPath() + 
        "&embedded=true\" frameborder=\"0\" height=\"500px\" width=\"100%\"></iframe></div>"
    }
}

case class Tex(f:File) extends  BlogFile {
    def render(): String = {
        val output = "pdflatex -output-directory=./generatedPdfs -output-format=pdf " + f.getAbsolutePath() !!
        
        val fileName = "./generatedPdfs/" + f.getName().split(".tex").head + ".pdf"
        println(fileName)

        val pdfFile = new File(fileName)
        assert( pdfFile.exists && pdfFile.canRead )
        val pdf = new Pdf(pdfFile)
        pdf.render()
        //s"<p>There was an error opening TeX file ${ f.getPath() }</p>" 
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