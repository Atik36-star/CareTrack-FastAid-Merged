/* CareTrack Doctor Workspace
   Frontend demonstration only. Records are stored in this browser's localStorage.
*/
"use strict";

const KEYS = {
  patients: "caretrackDoctorPatients_v1",
  history: "caretrackDoctorHistory_v1",
  medications: "caretrackDoctorMedications_v1",
  reports: "caretrackDoctorReports_v1",
  vitals: "caretrackDoctorVitals_v1",
  care: "caretrackDoctorCare_v1",
  emergency: "caretrackEmergencyAlerts_v1",
  icu: "caretrackICUPatients_v1",
  observations: "caretrackICUObservations_v1",
  notes: "caretrackICUNotes_v1",
  family: "caretrackFamilyUpdates_v1"
};

const $ = id => document.getElementById(id);
const read = key => {
  try { const value = JSON.parse(localStorage.getItem(key) || "[]"); return Array.isArray(value) ? value : []; }
  catch (error) { console.error("CareTrack read error", error); return []; }
};
const write = (key, value) => {
  try { localStorage.setItem(key, JSON.stringify(value)); return true; }
  catch (error) { console.error("CareTrack save error", error); alert("Could not save data in this browser."); return false; }
};
const id = () => "CT-" + Date.now() + "-" + Math.random().toString(36).slice(2,7);
const nowLocal = () => { const d = new Date(); d.setMinutes(d.getMinutes()-d.getTimezoneOffset()); return d.toISOString().slice(0,16); };
const today = () => new Date().toISOString().slice(0,10);
const dateText = value => { if (!value) return "—"; const d = new Date(value); return Number.isNaN(d.getTime()) ? String(value) : d.toLocaleString(); };
const escapeText = value => String(value ?? "");
function cell(row, value) { const td = document.createElement("td"); td.textContent = escapeText(value); row.appendChild(td); return td; }
function message(elementId, text, success=true) { const el=$(elementId); if(!el)return; el.textContent=text; el.classList.toggle("error",!success); }
function emptyRow(tbody, cols, text="No records yet.") { if(!tbody)return; const tr=document.createElement("tr"); const td=document.createElement("td"); td.colSpan=cols; td.className="empty-state"; td.textContent=text; tr.appendChild(td); tbody.appendChild(tr); }
function patientList() { return read(KEYS.patients); }
function getPatient(patientId) { return patientList().find(p=>p.patientId===patientId); }
function patientName(patientId) { return getPatient(patientId)?.name || patientId || "Unknown patient"; }
function makeSelect(selectId, includeICU=false) {
  const select=$(selectId); if(!select)return;
  const previous=select.value; select.replaceChildren(new Option("Select patient",""));
  let patients=patientList();
  if(includeICU){ const ids=read(KEYS.icu).map(p=>p.patientId); patients=patients.filter(p=>ids.includes(p.patientId)); }
  patients.forEach(p=>select.add(new Option(`${p.name} (${p.patientId})`,p.patientId)));
  if([...select.options].some(o=>o.value===previous))select.value=previous;
}
function refreshPatientSelects() {
  ["historyPatient","medPatient","reportPatient","vitalPatient","carePatient","icuPatient"].forEach(s=>makeSelect(s));
  ["observationPatient","icuNotesPatient","familyPatient"].forEach(s=>makeSelect(s,true));
}
function addRecord(key, record) { const data=read(key); data.push(record); return write(key,data); }
function statusBadge(value) { const span=document.createElement("span"); span.className="status-pill "+(value==="Recovered"?"status-info":value==="Monitoring"?"status-purple":"status-info"); span.textContent=value; return span; }

// Navigation
const sectionTitles={"dashboard":"Overview","patients":"Patients","medical-history":"Medical History","medications":"Medications","test-reports":"Test Reports","vital-signs":"Vital Signs","care-instructions":"Care Instructions","emergency":"Emergency Alert Center","icu":"ICU Command Center"};
function showSection(name) {
  if(!sectionTitles[name])name="dashboard";
  document.querySelectorAll(".content-section").forEach(s=>s.classList.toggle("active",s.id===name));
  document.querySelectorAll(".nav-link").forEach(a=>a.classList.toggle("active",a.dataset.section===name));
  $("pageTitle").textContent=sectionTitles[name]; history.replaceState(null,"","#"+name);
  $("sidebar").classList.remove("open"); window.scrollTo({top:0,behavior:"smooth"});
}

// ⚠️ শুধু এই লাইনটিই পরিবর্তন করা হয়েছে: FastAid লিংক ব্লক হবে না
document.querySelectorAll(".nav-link").forEach(a=>a.addEventListener("click",e=>{
  if(a.href && a.href.includes('/fastaid/')) return; // FastAid-এ যাওয়ার অনুমতি
  e.preventDefault();
  showSection(a.dataset.section);
}));

document.querySelectorAll("[data-go]").forEach(a=>a.addEventListener("click",e=>{e.preventDefault();showSection(a.dataset.go);}));
$("mobileMenu").addEventListener("click",()=>$("sidebar").classList.toggle("open"));
$("logoutBtn").addEventListener("click",()=>{if(confirm("Sign out of the Doctor workspace?")){sessionStorage.removeItem("caretrackUser");localStorage.removeItem("caretrackUser");alert("You have left the demo workspace.");}});
const userRaw=sessionStorage.getItem("caretrackUser")||localStorage.getItem("caretrackUser");
let userName="Dr. Rahman";
if(userRaw){try{const user=JSON.parse(userRaw); if(user.name)userName=user.name;}catch(e){}}
$("doctorName").textContent=userName;$("welcomeName").textContent=userName.replace(/^Dr\.?\s*/i,"");
$("todayLabel").textContent=new Date().toLocaleDateString(undefined,{weekday:"short",day:"numeric",month:"short",year:"numeric"});
if(location.hash && sectionTitles[location.hash.slice(1)])showSection(location.hash.slice(1));

// Patients
function renderPatients() {
  const tbody=$("patientTableBody"); if(!tbody)return; tbody.replaceChildren();
  const query=($("patientSearch")?.value||"").toLowerCase();
  const patients=patientList().filter(p=>[p.name,p.patientId,p.condition].join(" ").toLowerCase().includes(query));
  if(!patients.length)emptyRow(tbody,7,"No matching patients.");
  patients.forEach(p=>{
    const tr=document.createElement("tr");cell(tr,p.name);cell(tr,p.patientId);cell(tr,p.age);cell(tr,p.gender);cell(tr,p.condition);
    const td=document.createElement("td");td.appendChild(statusBadge(p.status));tr.appendChild(td);
    const action=cell(tr,"");const btn=document.createElement("button");btn.className="btn btn-outline";btn.textContent="Remove";btn.addEventListener("click",()=>deletePatient(p.patientId));action.appendChild(btn);tbody.appendChild(tr);
  });
  refreshPatientSelects(); renderDashboard();
}
function deletePatient(patientId) {
  if(!confirm("Remove this patient from the demo directory? Existing related records will remain."))return;
  write(KEYS.patients,patientList().filter(p=>p.patientId!==patientId));renderPatients();
}
$("showPatientForm").addEventListener("click",()=>{$("patientFormPanel").hidden=false;$("patientName").focus();});
$("closePatientForm").addEventListener("click",()=>$("patientFormPanel").hidden=true);
$("patientSearch").addEventListener("input",renderPatients);
$("patientForm").addEventListener("submit",e=>{
  e.preventDefault();const patientId=$("patientId").value.trim();
  if(patientList().some(p=>p.patientId.toLowerCase()===patientId.toLowerCase())){message("patientMessage","That patient ID already exists.",false);return;}
  const p={id:id(),name:$("patientName").value.trim(),patientId,age:Number($("patientAge").value),gender:$("patientGender").value,condition:$("patientCondition").value.trim(),status:$("patientStatus").value,createdAt:new Date().toISOString()};
  if(!p.name||!p.patientId||!p.condition||!Number.isFinite(p.age)){message("patientMessage","Please complete all required fields.",false);return;}
  if(addRecord(KEYS.patients,p)){message("patientMessage","Patient added successfully.");$("patientForm").reset();renderPatients();}
});

// Generic table renderers
function renderSimpleTable(key,tbodyId,columns,empty="No records yet.") {
  const tbody=$(tbodyId);if(!tbody)return;tbody.replaceChildren();const rows=read(key).slice().reverse();
  if(!rows.length){emptyRow(tbody,columns.length,empty);return;}
  rows.forEach(item=>{const tr=document.createElement("tr");columns.forEach(([field,format])=>cell(tr,format?format(item[field],item):item[field]));tbody.appendChild(tr);});
}
function bindRecordForm(formId,key,messageId,fields,build) {
  const form=$(formId);if(!form)return;
  form.addEventListener("submit",e=>{e.preventDefault();const values={};fields.forEach(f=>values[f]=$(f).value.trim());if(Object.values(values).some(v=>!v)){message(messageId,"Please complete all required fields.",false);return;}
    const patient=getPatient(values[fields[0]]);if(!patient){message(messageId,"Please select a valid patient.",false);return;}
    if(addRecord(key,build(values,patient))){message(messageId,"Record saved successfully.");form.reset();renderAll();}
  });
}
bindRecordForm("historyForm",KEYS.history,"historyMessage",["historyPatient","historyDiagnosis","historyDate","historyNotes"],(v,p)=>({id:id(),patientId:p.patientId,patientName:p.name,diagnosis:v.historyDiagnosis,date:v.historyDate,notes:v.historyNotes,createdAt:new Date().toISOString()}));
bindRecordForm("medicationForm",KEYS.medications,"medicationMessage",["medPatient","medName","medDosage","medFrequency","medStartDate"],(v,p)=>({id:id(),patientId:p.patientId,patientName:p.name,name:v.medName,dosage:v.medDosage,frequency:v.medFrequency,startDate:v.medStartDate,createdAt:new Date().toISOString()}));
bindRecordForm("reportForm",KEYS.reports,"reportMessage",["reportPatient","reportName","reportDate","reportResult"],(v,p)=>({id:id(),patientId:p.patientId,patientName:p.name,name:v.reportName,date:v.reportDate,result:v.reportResult,createdAt:new Date().toISOString()}));
bindRecordForm("vitalForm",KEYS.vitals,"vitalMessage",["vitalPatient","bloodPressure","heartRate","temperature","vitalDate"],(v,p)=>({id:id(),patientId:p.patientId,patientName:p.name,bloodPressure:v.bloodPressure,heartRate:v.heartRate,temperature:v.temperature,date:v.vitalDate,createdAt:new Date().toISOString()}));
bindRecordForm("careForm",KEYS.care,"careMessage",["carePatient","careTitle","careDescription","careDate"],(v,p)=>({id:id(),patientId:p.patientId,patientName:p.name,title:v.careTitle,description:v.careDescription,date:v.careDate,createdAt:new Date().toISOString()}));

// Emergency alert center
function renderEmergencyAlerts() {
  const container=$("emergencyAlertList");if(!container)return;const alerts=read(KEYS.emergency);
  const open=alerts.filter(a=>a.status!=="Resolved"),critical=open.filter(a=>a.priority==="Critical");
  $("pendingAlertCount").textContent=open.length;$("criticalAlertCount").textContent=critical.length;$("resolvedAlertCount").textContent=alerts.length-open.length;$("navAlertCount").textContent=open.length;$("dashboardAlerts").textContent=open.length;
  const select=$("responseAlertId"),prev=select.value;select.replaceChildren(new Option("Select an alert",""));
  open.forEach(a=>select.add(new Option(`${a.patientName||patientName(a.patientId)} — ${a.priority||"Routine"}`,a.id)));
  if([...select.options].some(o=>o.value===prev))select.value=prev;
  container.replaceChildren();if(!alerts.length){const p=document.createElement("p");p.className="empty-state";p.textContent="No emergency alerts available. Nurse-submitted alerts will appear here when shared in this browser.";container.appendChild(p);return;}
  alerts.slice().reverse().forEach(a=>{
    const card=document.createElement("div");card.className="alert-card "+(a.priority==="Critical"?"critical ":"")+(a.status==="Resolved"?"resolved":"");
    const h=document.createElement("h4");h.textContent=`${a.priority||"Routine"} alert · ${a.patientName||patientName(a.patientId)}`;
    const detail=document.createElement("p");detail.textContent=`Patient ID: ${a.patientId||"—"} · ${a.reason||a.type||"No details provided"}`;
    const time=document.createElement("p");time.textContent=`Received: ${dateText(a.createdAt)} · Status: ${a.status||"Pending"}`;
    card.append(h,detail,time);
    if(a.response){const r=document.createElement("p");r.textContent=`Doctor response: ${a.response}`;card.appendChild(r);}
    if(a.status!=="Resolved"){const actions=document.createElement("div");actions.className="alert-actions";
      const ack=document.createElement("button");ack.textContent="Acknowledge";ack.addEventListener("click",()=>updateAlert(a.id,"Acknowledged"));
      const resolve=document.createElement("button");resolve.textContent="Mark resolved";resolve.className="resolve-btn";resolve.addEventListener("click",()=>updateAlert(a.id,"Resolved"));actions.append(ack,resolve);card.appendChild(actions);}
    container.appendChild(card);
  });
}
function updateAlert(alertId,status){const alerts=read(KEYS.emergency),a=alerts.find(x=>x.id===alertId);if(!a)return;a.status=status;a.updatedAt=new Date().toISOString();if(write(KEYS.emergency,alerts))renderAll();}
$("doctorResponseForm").addEventListener("submit",e=>{e.preventDefault();const alertId=$("responseAlertId").value,response=$("doctorResponse").value.trim();const alerts=read(KEYS.emergency),a=alerts.find(x=>x.id===alertId);if(!a||!response){message("doctorResponseMessage","Choose an open alert and enter a response.",false);return;}a.response=response;a.respondedAt=new Date().toISOString();if(a.status!=="Resolved")a.status="Acknowledged";if(write(KEYS.emergency,alerts)){message("doctorResponseMessage","Response saved.");$("doctorResponseForm").reset();renderAll();}});
$("demoAlertBtn").addEventListener("click",()=>{const patients=patientList();if(!patients.length){alert("Add a patient first to create a demo alert.");return;}const p=patients[0];const alerts=read(KEYS.emergency);alerts.push({id:id(),patientId:p.patientId,patientName:p.name,priority:"Critical",reason:"Demo: nurse reported sudden deterioration. Verify the patient record and respond.",status:"Pending",createdAt:new Date().toISOString()});if(write(KEYS.emergency,alerts))renderAll();});

// ICU admissions
function renderICUPatients(){
  const tbody=$("icuPatientTableBody");if(!tbody)return;const records=read(KEYS.icu);tbody.replaceChildren();
  if(!records.length)emptyRow(tbody,6,"No ICU patients have been added.");
  records.forEach(p=>{const tr=document.createElement("tr");[p.patientName,p.patientId,p.bed,p.admissionDate,p.support].forEach(v=>cell(tr,v));const td=cell(tr,"");const b=document.createElement("button");b.className="btn btn-outline";b.textContent="Discharge";b.addEventListener("click",()=>dischargeICU(p.id));td.appendChild(b);tbody.appendChild(tr);});
  $("icuPatientCount").textContent=records.length;$("ventilatorPatientCount").textContent=records.filter(p=>p.support==="Ventilator").length;
  ["observationPatient","icuNotesPatient","familyPatient"].forEach(s=>makeSelect(s,true));
}
$("icuPatientForm").addEventListener("submit",e=>{e.preventDefault();const patientId=$("icuPatient").value,p=getPatient(patientId),records=read(KEYS.icu),bed=$("icuBed").value.trim();if(!p){message("icuPatientMessage","Select a valid patient.",false);return;}if(records.some(x=>x.patientId===patientId)){message("icuPatientMessage","This patient is already listed in ICU.",false);return;}if(records.some(x=>x.bed.toLowerCase()===bed.toLowerCase())){message("icuPatientMessage","That ICU bed is already assigned.",false);return;}records.push({id:id(),patientId,patientName:p.name,bed,admissionDate:$("icuAdmissionDate").value,support:$("icuSupport").value,createdAt:new Date().toISOString()});if(write(KEYS.icu,records)){message("icuPatientMessage","Patient added to ICU list.");$("icuPatientForm").reset();renderAll();}});
function dischargeICU(recordId){if(!confirm("Remove this patient from the ICU list?"))return;write(KEYS.icu,read(KEYS.icu).filter(p=>p.id!==recordId));renderAll();}

// ICU observations
function renderICUObservations(){const tbody=$("icuObservationTableBody");if(!tbody)return;tbody.replaceChildren();const records=read(KEYS.observations).slice().reverse();if(!records.length){emptyRow(tbody,7,"No observations recorded yet.");return;}records.forEach(o=>{const tr=document.createElement("tr");[o.patientName,`${o.spo2}%`,`${o.fio2}%`,o.peep,o.respiratoryRate,o.tidalVolume,dateText(o.time)].forEach(v=>cell(tr,v));tbody.appendChild(tr);});}
$("icuObservationForm").addEventListener("submit",e=>{e.preventDefault();const patientId=$("observationPatient").value,p=getPatient(patientId),icu=read(KEYS.icu).some(x=>x.patientId===patientId);if(!p||!icu){message("observationMessage","Select a current ICU patient.",false);return;}const observation={id:id(),patientId,patientName:p.name,spo2:Number($("spo2").value),fio2:Number($("fio2").value),peep:Number($("peep").value),respiratoryRate:Number($("respiratoryRate").value),tidalVolume:Number($("tidalVolume").value),time:$("observationTime").value,createdAt:new Date().toISOString()};if(Object.values(observation).some(v=>typeof v==="number"&&!Number.isFinite(v))){message("observationMessage","Check the numeric values.",false);return;}if(addRecord(KEYS.observations,observation)){message("observationMessage","Observation saved.");$("icuObservationForm").reset();$("observationTime").value=nowLocal();renderICUObservations();}});

// ICU notes and family updates
function renderICUNotes(){const box=$("icuClinicalNotesList");if(!box)return;box.replaceChildren();const records=read(KEYS.notes).slice().reverse();if(!records.length){const p=document.createElement("p");p.className="empty-state";p.textContent="No clinical notes yet.";box.appendChild(p);return;}records.forEach(n=>{const c=document.createElement("div");c.className="note-card";const h=document.createElement("h4");h.textContent=`${n.patientName} · ${n.author||"Doctor"}`;const p=document.createElement("p");p.textContent=n.text;const t=document.createElement("p");t.textContent=`Recorded: ${dateText(n.createdAt)}`;c.append(h,p,t);box.appendChild(c);});}
$("icuNotesForm").addEventListener("submit",e=>{e.preventDefault();const patientId=$("icuNotesPatient").value,p=getPatient(patientId),text=$("icuClinicalNotes").value.trim();if(!p||!read(KEYS.icu).some(x=>x.patientId===patientId)||!text){message("icuNotesMessage","Choose an ICU patient and enter a note.",false);return;}if(addRecord(KEYS.notes,{id:id(),patientId,patientName:p.name,text,author:userName,createdAt:new Date().toISOString()})){message("icuNotesMessage","Clinical note saved.");$("icuNotesForm").reset();renderICUNotes();}});
function renderFamilyUpdates(){const box=$("familyUpdateList");if(!box)return;box.replaceChildren();const updates=read(KEYS.family);$("pendingFamilyCount").textContent=updates.filter(u=>u.status==="Draft").length;if(!updates.length){const p=document.createElement("p");p.className="empty-state";p.textContent="No family updates drafted.";box.appendChild(p);return;}updates.slice().reverse().forEach(u=>{const c=document.createElement("div");c.className="family-card";const h=document.createElement("h4");h.textContent=u.patientName;const p=document.createElement("p");p.textContent=u.text;const s=document.createElement("p");s.textContent=`Status: ${u.status} · ${dateText(u.createdAt)}`;c.append(h,p,s);if(u.status==="Draft"){const b=document.createElement("button");b.className="btn btn-primary";b.textContent="Approve update";b.addEventListener("click",()=>approveFamilyUpdate(u.id));c.appendChild(b);}box.appendChild(c);});}
function approveFamilyUpdate(updateId){const updates=read(KEYS.family),u=updates.find(x=>x.id===updateId);if(!u)return;u.status="Approved";u.approvedAt=new Date().toISOString();u.approvedBy=userName;if(write(KEYS.family,updates))renderFamilyUpdates();}
$("familyUpdateForm").addEventListener("submit",e=>{e.preventDefault();const patientId=$("familyPatient").value,p=getPatient(patientId),text=$("familyUpdateText").value.trim();if(!p||!read(KEYS.icu).some(x=>x.patientId===patientId)||!text){message("familyUpdateMessage","Choose an ICU patient and write an update.",false);return;}if(addRecord(KEYS.family,{id:id(),patientId,patientName:p.name,text,status:"Draft",createdAt:new Date().toISOString()})){message("familyUpdateMessage","Saved as a draft for doctor review.");$("familyUpdateForm").reset();renderFamilyUpdates();}});

// Dashboard
function renderDashboard(){
  const patients=patientList(),icu=read(KEYS.icu),alerts=read(KEYS.emergency),active=patients.filter(p=>p.status==="Active").length,monitoring=patients.filter(p=>p.status==="Monitoring").length,recovered=patients.filter(p=>p.status==="Recovered").length,total=patients.length;
  $("totalPatients").textContent=total;$("activePatients").textContent=active;$("dashboardICU").textContent=icu.length;$("dashboardAlerts").textContent=alerts.filter(a=>a.status!=="Resolved").length;$("donutTotal").textContent=total;
  $("legendActive").textContent=active;$("legendMonitoring").textContent=monitoring;$("legendRecovered").textContent=recovered;
  const a=total?active/total*100:0,m=total?monitoring/total*100:0;$("patientDonut").style.background=`conic-gradient(#16866e 0 ${a}%,#e9b04a ${a}% ${a+m}%,#b8c4cc ${a+m}% 100%)`;
  const recent=$("recentPatientsBody");recent.replaceChildren();patients.slice().reverse().slice(0,5).forEach(p=>{const tr=document.createElement("tr");[p.name,p.patientId,p.age,p.condition].forEach(v=>cell(tr,v));const td=document.createElement("td");td.appendChild(statusBadge(p.status));tr.appendChild(td);recent.appendChild(tr);});if(!recent.children.length)emptyRow(recent,5,"No patients registered yet.");
  const priority=$("priorityList");priority.replaceChildren();const open=alerts.filter(x=>x.status!=="Resolved");const critical=open.filter(x=>x.priority==="Critical");const icuVent=icu.filter(x=>x.support==="Ventilator");
  const items=[...critical.map(x=>({title:`Critical alert: ${x.patientName}`,sub:x.reason||"Review emergency alert",target:"emergency"})),...icuVent.map(x=>({title:`Ventilator support: ${x.patientName}`,sub:`Bed ${x.bed} · Review recorded observations`,target:"icu"}))].slice(0,5);
  if(!items.length){const p=document.createElement("p");p.className="empty-state";p.textContent="No priority items at the moment.";priority.appendChild(p);}else items.forEach(item=>{const a=document.createElement("a");a.href="#"+item.target;a.className="priority-item";a.style.textDecoration="none";a.innerHTML="";const left=document.createElement("div");const strong=document.createElement("strong");strong.textContent=item.title;const small=document.createElement("small");small.textContent=item.sub;left.append(strong,small);const arrow=document.createElement("span");arrow.textContent="→";arrow.style.color="var(--teal)";a.append(left,arrow);a.addEventListener("click",e=>{e.preventDefault();showSection(item.target);});priority.appendChild(a);});
}
function renderAll(){renderSimpleTable(KEYS.history,"historyTableBody",[["patientName"],["diagnosis"],["date"],["notes"]]);renderSimpleTable(KEYS.medications,"medicationTableBody",[["patientName"],["name"],["dosage"],["frequency"],["startDate"]]);renderSimpleTable(KEYS.reports,"reportTableBody",[["patientName"],["name"],["date"],["result"]]);renderSimpleTable(KEYS.vitals,"vitalTableBody",[["patientName"],["bloodPressure"],["heartRate"],["temperature"],["date",dateText]]);renderSimpleTable(KEYS.care,"careTableBody",[["patientName"],["title"],["description"],["date"]]);renderEmergencyAlerts();renderICUPatients();renderICUObservations();renderICUNotes();renderFamilyUpdates();renderDashboard();}
function seedDemoPatients(){if(patientList().length)return;write(KEYS.patients,[
  {id:id(),name:"Rahim Ahmed",patientId:"PT001",age:52,gender:"Male",condition:"Hypertension",status:"Active",createdAt:new Date().toISOString()},
  {id:id(),name:"Fatima Akter",patientId:"PT002",age:45,gender:"Female",condition:"Diabetes",status:"Active",createdAt:new Date().toISOString()},
  {id:id(),name:"Karim Hasan",patientId:"PT003",age:60,gender:"Male",condition:"Stroke Recovery",status:"Monitoring",createdAt:new Date().toISOString()}
]);}
seedDemoPatients();
refreshPatientSelects();
$("vitalDate").value=nowLocal();$("observationTime").value=nowLocal();
renderPatients();renderAll();

// Update the dashboard when another CareTrack role writes shared demo data in this browser.
window.addEventListener("storage",event=>{if(Object.values(KEYS).includes(event.key))renderAll();});