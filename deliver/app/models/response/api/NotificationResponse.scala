package models.response.api

import play.api.libs.json.Json

class NotificationResponse(
    code:Int,
    message:String,
    id:String,
    redirect:String,
    messageId:Long,
    title:String,
    content:String,
    icon:String
    ) extends ApiCommonResponse(code, message){
  
  override def resJson = {
    Json.obj(
        "code" -> code,
        "message" -> message,
        "id" -> id,
        "redirect" -> redirect,
        "pushLogId" -> messageId,
        "notification" -> Json.obj(
          "title" -> title,
          "message" -> content,
          "icon" -> icon,
          "tag" -> "push-notification-tag"
        )
    )
  }
}