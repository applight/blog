import java.io.File
import java.nio.file.Files
import java.nio.file.Paths
import scala.util.matching.Regex
import sys.process._
import me.thenshow._

object Blog {
  def getFilesInDir( dir: String ) : List[File] = {
    val d = new File(dir)

    if ( d.exists && d.isDirectory ) {
      d.listFiles.filter(_.isFile).toList
      .sortBy( _.lastModified );
    } else {
      List[File]()
    }
  }

  def fileHandler( f:File ):String = {
    assert( f.exists && f.canRead )

    val probeFileType = Files.probeContentType( Paths.get(f.getPath()) )
    val fileCmdFileType = "file " + f.getPath() !!

    val tex: Regex = "(text/x-tex)".r
    val ascii:Regex = "(ASCII)".r
    val image:Regex = "([pP]+[nN]+[gG]+)|([jJ]+[pP]+[eE]*[gG]+)|([iI]+[mM]+[aA]+[gG]+[eE]+)".r

    val searchable:String = if (probeFileType == null) { fileCmdFileType } else { fileCmdFileType + probeFileType }

    val blogFile:BlogFile = image.findFirstMatchIn( searchable ) match {
      case Some(_) => new Image(f)
      case None => tex.findFirstMatchIn( searchable ) match {
        case Some(_) => new Tex(f)
        case None => ascii.findFirstMatchIn( searchable ) match {
          case Some(_) => new ASCII(f)
          case None => new Unknown(f)
        }
      }
    }

    blogFile.render()
    /*f.getName() +
      ":" + 
      Files.probeContentType(Paths.get(f.getPath())) + 
      ("file " + f.getPath() !!)
    */
  }

  def main( args: Array[String] ) = {
    val result = this.getFilesInDir("./content").map(this.fileHandler)
    result.reverseMap( println ) 
    println("Exiting");
  }
}
