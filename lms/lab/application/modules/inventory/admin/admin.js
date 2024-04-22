async function Inventory_Admin() {
  Core_State_Set("inventory", "inventory-table-header", [
    UI_Language_String("inventory/module", "table no"),
    UI_Language_String("inventory/module", "table item code"),
    UI_Language_String("inventory/module", "table item name"),
    UI_Language_String("inventory/module", "programs levels")
  ]);

  var module = Core_State_Get("inventory", "container");
  const container = UI_Element_Find(module, "inventory-admin");

  const itemDetailContainer = UI_Element_Create(
    "inventory/components/inventory-detail",
    {
      btnAction: UI_Language_String("inventory/module", "button save"),
    }
  );

  // Init search UI
  const groupPrograms = Core_State_Get("inventory", "groupPrograms");
  const searchElement = createSearchElement("admin", {
    showCenter: false,
    showProgram: false,
    onSearch: (e) => { btnClicked(e,"admin");  showItemsAdminTable(); },
    programData: groupPrograms,
  });

  const exportButton = UI_Element_Create("core/button-small-stretch",{text:UI_Language_String("inventory/components/module","button export")});
  Document_Element_SetObject(exportButton, "state", "admin");
  exportButton.onclick = (e) => { exportExecl(e);}
  UI_Element_Find(searchElement, "action-container").appendChild(exportButton);

  const loadDataButton = UI_Element_Create("core/button-small-stretch",{text:UI_Language_String("inventory/components/module","button load")});
  loadDataButton.style.justifyContent = "space-around";
  loadDataButton.onclick = async function(){
    var div = document.createElement("div");
    div.classList.add("loader");
    div.style.cssText = " font-size: 4px; position: absolute; margin-left: 200px";
    loadDataButton.appendChild(div);
    
    var updateStatus = await Core_Api("Inventory_Items_Create_Update_FromD365&filter=true");
    if(updateStatus)
    {
      loadDataButton.removeChild(loadDataButton.firstElementChild);
      //UPDATE GROUP DATA
      var groups = await Core_Api("Inventory_Get_Groups_Item");
      Core_State_Set("inventory", "groups", groups);
      reloadGroupsData()
      showItemsAdminTable();
      showAlertMessage("Data has been updated");
    }
    else showAlertMessage("No Data has been updated!!");
  }
  UI_Element_Find(searchElement, "action-container").appendChild(loadDataButton);

  container.appendChild(searchElement);

  // Init List Item
  const itemListContainer = UI_Element_Create(
    "inventory/components/inventory-list"
  );
  container.appendChild(itemListContainer);

  container.appendChild(itemDetailContainer);
  UI_Element_Find(itemDetailContainer, "btn-action").onclick = updateItemInfo;
  var btnExport = UI_Element_Find(itemDetailContainer, "btn-export");
  btnExport.innerHTML =  UI_Language_String("inventory/module", "button new program");
  btnExport.onclick = async () => {

    const popupContent = UI_Element_Create("inventory/popup-confirm/popup-confirm", {
      title: "Assign new program/level",
    });
    var button = 
    {
     text: UI_Language_String("inventory/module", "button save"),
     onclick: async() => { UI_Popup_Close(popup); await AssignProgramItem()}
    }
    const contentdiv = UI_Element_Find(popupContent, "content");
    contentdiv.innerHTML = "";

    // PROGRAM
    const programs = Core_State_Get("inventory", "groupPrograms");
    const programElement = UI_Element_Create("core/control-dropdown-plain");
    programElement.dataset.uid = "search-program";

    // LEVELS
    const levelElement = UI_Element_Create("core/control-dropdown-plain");
    levelElement.dataset.uid = "search-levels";

    contentdiv.appendChild(programElement)
    contentdiv.appendChild(levelElement);

    // SETUP OPTION SELECT
    Document_Select_AddOption(
      programElement,
      UI_Language_String("inventory", "search any"),
      ""
    );
    Document_Select_AddOption(programElement, "---", "").disabled = true;
    Document_Select_OptionsFromObjects(programElement, programs, "name", false);
    Core_State_Set("inventory", ["admin", "item-detail", "assign-new-program", "program"], programElement.value);
    programElement.onchange = (e) => {
      const value = e.target.value;
      Core_State_Set("inventory", ["admin", "item-detail", "assign-new-program", "program"], value);
      displayLevels(contentdiv, ["admin", "item-detail", "assign-new-program"], value, programs);
    };
  
    // LEVELS
    displayLevels(contentdiv, ["admin", "item-detail", "assign-new-program"], programElement.value, programs);
  

    const popup = await UI_Popup_Create(
      {content:popupContent},
      [button],
      ""
    );
  }
}

async function showItemsAdminTable() {
  var module = Core_State_Get("inventory", "container");
  const container = UI_Element_Find(module, "inventory-admin");
  UI_Element_Find(container, "item-detail").style.visibility = "hidden";

  const itemListContainer = UI_Element_Find(container, "item-list-container");
  itemListContainer.style.visibility = "visible";
  itemListContainer.innerHTML = "";
  const itemlist = document.createElement("div");
  itemlist.classList.add("container-column");
  itemlist.style.width = "100%";
  itemlist.style.overflow = "hidden auto";
  itemListContainer.appendChild(itemlist);
  const table = UI_Table("standard", { fixed: true });
  itemlist.appendChild(table);

  const headerRow = UI_Table_Row(table);
  headerRow.style.position = "sticky";
  headerRow.style.top = 0;

  // CREATE TABLE HEADER
  var header = Core_State_Get("inventory", "inventory-table-header");
  header.forEach((item) => {
    const cell = UI_Table_Cell(headerRow, { type: "header" });
    cell.innerHTML = item;
  });

    const data =
    (await Core_Api("Inventory_Items_Search", {
      search: {
        group: Core_State_Get("inventory", ["admin", "group"]),
        program: Core_State_Get("inventory", ["admin", "program"]),
        level: Core_State_Get("inventory", ["admin", "level"]),
      },
    })) || [];

  Core_State_Set("inventory", ["admin", "items"], data);
  Core_State_Set("inventory", ["admin", "selectedItemIndex"], null);

  if (data && data.length > 0) {
    for (let i = 0; i < data.length; i++) {
      const { item_name = "", item_code = "" ,item_group = "", programs_levels = ""} = data[i];
      const row = UI_Table_Row(table, { selectable: true }, () =>
        itemAdminDetail(i)
      );
      const noCell = UI_Table_Cell(row);
      noCell.innerHTML = i + 1;

      var itemCodeCell = UI_Table_Cell(row);
      itemCodeCell.innerHTML = item_code;

      var itemNameCell = UI_Table_Cell(row);
      itemNameCell.innerHTML = item_name;

      var itemProgramsLevels = "";
      for (let i = 0; i < programs_levels.length; i++) {
        const { program = "", level = "", id = ""} = programs_levels[i];
        if( i == programs_levels.length - 1) itemProgramsLevels += program + "_" + level;
        else itemProgramsLevels += program + "_" + level + " , ";
      }
      var programsLevelsCell = UI_Table_Cell(row);
      programsLevelsCell.innerHTML = itemProgramsLevels;
    }
  } else {
    const row = UI_Table_Row(table);
    const messageCell = UI_Table_Cell(row);
    messageCell.colSpan = header.length;
    messageCell.style.height = "300px";
    messageCell.innerText = "No items";
  }
}

function itemAdminDetail(index) {
  Core_State_Set("inventory", ["admin", "selectedItemIndex"], index);
  const item = Core_State_Get("inventory", ["admin", "items"])[index];
  const { item_code, item_name, item_group, programs_levels} = item || {};

  var module = Core_State_Get("inventory", "container");
  const container = UI_Element_Find(module, "inventory-admin");
  const itemDetailContainer = UI_Element_Find(container, "item-detail");
  itemDetailContainer.style.visibility = "visible";
  itemDetailContainer.style.justifyContent = "space-between";
  const itemInfoContainer = UI_Element_Find(itemDetailContainer, "item-info");
  itemInfoContainer.classList.remove("container-column");
  itemInfoContainer.classList.add("container-row");
  itemInfoContainer.style.flexWrap = "Wrap";
  itemInfoContainer.style.flex = null;
  itemInfoContainer.innerHTML = "";

  [
    {
      value: item_code,
      name: "code",
    },
    {
      value: item_name,
      name: "name",
    },
    {
      value: item_group,
      name: "group",
    }
  ].forEach((item) => {
    Core_State_Set(
      "inventory",
      ["admin", "item-detail", item.name],
      item.value
    );
  });

  // ITEM CODE
  var code = UI_Element_Create("core/control-box-plain");
  code.innerHTML = item_code;

  codedetail = UI_Element_Create("inventory/components/item-detail", {
    label: UI_Language_String("inventory/module", "table item code"),
  });
  codedetail.appendChild(code);
  itemInfoContainer.appendChild(codedetail);

  // ITEM NAME
  var name = UI_Element_Create("core/control-box-plain");
  name.innerHTML = item_name;

  namedetail = UI_Element_Create("inventory/components/item-detail", {
    label: UI_Language_String("inventory/module", "table item name"),
  });
  namedetail.appendChild(name);
  itemInfoContainer.appendChild(namedetail);


  // TABLE PROGRAM LEVEL 
  const table = UI_Table("standard", { fixed: true });
  itemInfoContainer.appendChild(table);

  const headerRow = UI_Table_Row(table);
  headerRow.style.position = "sticky";
  headerRow.style.top = 0;

  // CREATE TABLE HEADER
  const cellHeaderProgram = UI_Table_Cell(headerRow, { type: "header" });
  cellHeaderProgram.innerHTML =  UI_Language_String("inventory/components/module", "search program");
  
  const cellHeaderLevel = UI_Table_Cell(headerRow, { type: "header" });
  cellHeaderLevel.innerHTML =  UI_Language_String("inventory/components/module", "search level");

  // PROGRAM
  const programs = Core_State_Get("inventory", "groupPrograms");
  const programElement = UI_Element_Create("core/control-dropdown-plain");
  programElement.dataset.uid = "search-program";
  Document_Select_AddOption(
    programElement,
    UI_Language_String("inventory", "search any"),
    "Any"
  );
  Document_Select_AddOption(programElement, "---", "").disabled = true;
  Document_Select_OptionsFromObjects(programElement, programs, "name", false);


  // LEVELS
  const levelElement = UI_Element_Create("core/control-dropdown-plain");
  levelElement.dataset.uid = "search-levels";

  if (programs_levels && programs_levels.length > 0) {
    for (let i = 0; i < programs_levels.length; i++) {
      const { program = "", level = "", id = ""} = programs_levels[i];
      const row = UI_Table_Row(table);


      var programCell = UI_Table_Cell(row);
      var programElementClone = programElement.cloneNode(true);
      programElementClone.dataset.id = "program_" + item_code + "_" + i;
      programElementClone.dataset.rowid = id;
      programElementClone.value = program ?? "Any";

      programElementClone.onchange = (e) => {
        Core_State_Set(
          "inventory",
          ["admin", "item-detail", "programs-levels", e.target.dataset.rowid, "program"],
          e.target.value
        );
       
        displayLevels(
          e.target.parentElement.parentElement,
          ["admin", "item-detail", "programs-levels", e.target.dataset.rowid],
          e.target.value,
          programs
        );
      };

      programCell.appendChild(programElementClone);

      Core_State_Set(
        "inventory",
        ["admin", "item-detail", "programs-levels" , programElementClone.dataset.rowid, "program"],
        programElementClone.value
      );

      var levelCell = UI_Table_Cell(row);
      var levelElementClone = levelElement.cloneNode(true);
      levelElementClone.dataset.id = "level_" + item_code + "_" + i;
      levelElementClone.dataset.rowid = id;
      levelCell.appendChild(levelElementClone);

      displayLevels(
        levelCell,
        ["admin", "item-detail", "programs-levels", levelElementClone.dataset.rowid],
        programElementClone.value,
        programs
      );

      levelElementClone.value = level ?? "Any";
      Core_State_Set(
        "inventory",
        ["admin", "item-detail", "programs-levels", levelElementClone.dataset.rowid, "level"],
        levelElementClone.value
      );
    }
  } else {
    const row = UI_Table_Row(table);
    const messageCell = UI_Table_Cell(row);
    messageCell.colSpan = 2;
    messageCell.style.height = "100px";
    messageCell.innerText = "No items";
  }

}

async function updateItemInfo() {
  const isAccept = await UI_Popup_Confirm(
    UI_Language_String("inventory/module", "update information"),
    UI_Language_String("inventory/module", "update confirm text"),
  );
  if (!isAccept) return;

  const itemDetail = Core_State_Get("inventory", ["admin", "item-detail"]);

  res = await Core_Api("Inventory_Item_Update", {
    item: {
      item_code: itemDetail.code,
      item_group: itemDetail.group,
      item_name: itemDetail.name,
      programs_levels: itemDetail["programs-levels"],
    },
  });
  if (res) {
    showAlertMessage("Update Item SuccessFuly !");
    showItemsAdminTable();
    var module = Core_State_Get("inventory", "container");
    const container = UI_Element_Find(module, "inventory-admin");
    UI_Element_Find(container, "item-detail").style.visibility = "visible";
  } else {
    showAlertMessage("Operation error !!!");
  }
}


async function AssignProgramItem(){
  var program = Core_State_Get("inventory",["admin","item-detail","assign-new-program","program"],null);
  var level   = Core_State_Get("inventory",["admin","item-detail","assign-new-program","level"],null);
  var item_code = Core_State_Get("inventory",["admin","item-detail","code"],null);
  var result = await Core_Api("Inventory_Items_Assign_Program",{
    item_code:item_code,
    program:program,
    level:level
  });
  if (result) {
    showAlertMessage("Assign program SuccessFuly !");
    var currentSelect = Core_State_Get("inventory", ["admin", "selectedItemIndex"], null);
    await showItemsAdminTable();
    itemAdminDetail(currentSelect) ;
  } else {
    showAlertMessage("Operation error !!!");
  }
}