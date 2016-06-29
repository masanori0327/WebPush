package models.service.api

import anorm._
import anorm.SqlParser._
import models.response.api.NotificationResponse
import models.response.api.ApiCommonResponse
import org.joda.time.DateTime
import org.joda.time.format.DateTimeFormat
import org.joda.time.DateTimeZone
import play.api.db._
import play.api.Play.current

class NotificationService {
  
  /**
   * Notification Json 作成
   */
  def get(id:String) = {
    DB.withConnection { implicit c =>
      val webpropertyId = SQL("SELECT webproperty_id FROM subscriber_list WHERE subscription_id = {id}")
      .on("id" -> id).as(str("webproperty_id") *).head
      
      val notification = SQL("SELECT * FROM send_history WHERE webproperty_id = {id} order by send_datetime desc")
      .on("id" -> webpropertyId).as(int("send_history_table_id")~str("title")~str("message")~str("link") map(flatten)*).head
      
      val icon = SQL("SELECT icon FROM google_analytics_account WHERE webproperty_id = {id}")
      .on("id" -> webpropertyId).as(str("icon")*).head
      
      val notificationResponse = new NotificationResponse(
        ApiCommonResponse.CODE_SUCCESS,
        "success!",
        id,
        notification._4,
        notification._1,
        notification._2,
        notification._3,
        icon    // CDNに置いておく
      )
    
      notificationResponse.resJson
    }
  }
  
  /**
   * {show,click} log Insert
   */
  def insertLog(id:String, msg:String, table:String) = {
    DB.withConnection { implicit c =>
      val dateString = DateTimeFormat.forPattern("yyyy-MM-dd HH:mm:ss").print(new DateTime(DateTimeZone.forID("Asia/Tokyo")))
      SQL("INSERT INTO " + table + " SET send_history_table_id = {msgId} , subscription_id = {id} , add_datetime = {now}")
      .on("msgId" -> msg.toInt, "id" -> id, "now" -> dateString).executeInsert()
    }
    
    val res = new ApiCommonResponse(ApiCommonResponse.CODE_SUCCESS, "success!")
    res.resJson
  }
  
  /**
   * error log Insert
   */
  def insertErrorLog(id:String, error:String) = {
    DB.withConnection { implicit c =>
      val dateString = DateTimeFormat.forPattern("yyyy-MM-dd HH:mm:ss").print(new DateTime(DateTimeZone.forID("Asia/Tokyo")))
      SQL("INSERT INTO error_notification_log SET error = {error} , subscription_id = {id} , add_datetime = {now}")
      .on("error" -> error, "id" -> id, "now" -> dateString).executeInsert()
    }
    
    val res = new ApiCommonResponse(ApiCommonResponse.CODE_SUCCESS, "success!")
    res.resJson
  }
}