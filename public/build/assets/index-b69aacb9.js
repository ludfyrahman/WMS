import{d as c}from"./dom-7f8f01b1.js";(function(t){function r(a){const s=t(a).clone().html(""),e=t(t("<div></div>").append(s)).find(t(s).data("selector")).length;t(a).data("state")===void 0&&(t(a).data("state",e?"leave":"enter"),e?t(a).show():t(a).hide()),e&&t(a).data("state")==="leave"?(t(a).data("state","enter"),t(a).show(),t(a).addClass(t(a).data("enter-from")),setTimeout(()=>{t(a).addClass(t(a).data("enter")),t(a).addClass(t(a).data("enter-to")),t(a).removeClass(t(a).data("enter-from")),setTimeout(()=>{t(a).removeClass(t(a).data("enter"))},parseFloat(t(a).css("transition-duration"))*1e3)})):!e&&t(a).data("state")==="enter"&&(t(a).data("state","leave"),t(a).addClass(t(a).data("leave-from")),setTimeout(()=>{t(a).addClass(t(a).data("leave")),t(a).addClass(t(a).data("leave-to")),t(a).removeClass(t(a).data("leave-from")),setTimeout(()=>{t(a).removeClass(t(a).data("leave")),t(a).attr("class",(t(a).attr("class")!==void 0?t(a).attr("class"):"").split(" ").filter(i=>i.search("mt-")===-1).join(" ")),setTimeout(()=>{t(a).hide()},100)},parseFloat(t(a).css("transition-duration"))*1e3)}))}const n=new MutationObserver(a=>{a.forEach(async function(s){s.type==="attributes"&&r(s.target)})});t("[data-transition]").each(function(){n.observe(this,{attributes:!0}),r(this)})})(c);