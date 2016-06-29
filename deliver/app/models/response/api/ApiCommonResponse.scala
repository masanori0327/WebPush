package models.response.api

import play.api.libs.json.Json

class ApiCommonResponse(val code :Int, val message:String){
  def resJson = Json.obj("code" -> code, "message" -> message)
}

object ApiCommonResponse{
  val CODE_SUCCESS = 1
  val CODE_ERROR = -1
  val CODE_PENDING = 2
}