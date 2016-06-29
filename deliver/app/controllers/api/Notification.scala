package controllers.api

import play.api._
import play.api.mvc._
import models.response.api.ApiCommonResponse
import models.service.api.NotificationService

class Notification extends Controller {
  
  /**
   * error response Json
   */
  def errorResponse(message:String) = {
    val res = new ApiCommonResponse(ApiCommonResponse.CODE_ERROR, message)
    res.resJson
  }

  /**
   * Notification response Json
   */
  def get = Action { implicit request =>
    val id = request.getQueryString("id")
    var result = errorResponse("error")
    if(id == None) result = errorResponse("not subscriptionId")
    else{
      try{
        result = new NotificationService().get(id.get)
      }catch {
        case e:Exception => result = errorResponse("not notification [" + e.getMessage + "]")
      }
    }
    Ok(result).withHeaders("Access-Control-Allow-Origin" -> "*", "Content-Type" -> "application/json;charset=UTF-8")
  }
  
  /**
   * show response Json
   */
  def show = Action { implicit request =>
    showClickRoute(request, "show")
  }
  
  /**
   * click response Json
   */
  def click = Action { request =>
    showClickRoute(request, "click")
  }
  
  /**
   * {show,click} Route
   */
  def showClickRoute(req:Request[AnyContent], typ:String) = {
    val id = req.getQueryString("id")
    val msg = req.getQueryString("msg")
    var result = errorResponse("error")
    if(id == None) result = errorResponse("not subscriptionId")
    else if(msg == None) result = errorResponse("not notificationId")
    else{
      try{
        result = new NotificationService().insertLog(id get, msg get, typ+"_notification_log")
      }catch{
        case e:Exception => {
          result = errorResponse(typ + " error [" + e.getMessage + "]")
          Logger.warn(e.getMessage)
        }
      }
    }
    Ok(result).withHeaders("Access-Control-Allow-Origin" -> "*", "Content-Type" -> "application/json;charset=UTF-8")
  }
  
  /**
   * error response Json
   */
  def error = Action { implicit request =>
    val id = request.getQueryString("id")
    val error = request.getQueryString("error")
    var result = errorResponse("error")
    if(id == None) result = errorResponse("not subscriptionId")
    else if(error == None) result = errorResponse("not notificationId")
    else{
      try{
        result = new NotificationService().insertErrorLog(id get, error get)
      }catch{
        case e:Exception => {
          result = errorResponse("error error [" + e.getMessage + "]")
          Logger.warn(e.getMessage)
        }
      }
    }
    Ok(result).withHeaders("Access-Control-Allow-Origin" -> "*", "Content-Type" -> "application/json;charset=UTF-8")
  }
}
