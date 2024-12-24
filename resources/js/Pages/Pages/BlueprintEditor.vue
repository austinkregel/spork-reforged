<template>

    <AppLayout title="Dashboard">
        <header class="header">
            <h3>Drawflow Example vue3</h3>
            <button type="primary"   @click="exportEditor">Export</button>
        </header>
        <div class="flex w-full" style="min-height: calc(100vh - 65px);">
        <div class="flex w-full">
            <div  class="column w-1/3">
                <ul class="grid grid-cols-3 gap-4">
                    <li
                        v-for="n in listNodes"
                        :key="n"
                        draggable="true"
                        :data-node="n.component"
                        @dragstart="drag($event)"
                        class="drag-drawflow"
                    >

                        <!-- :data-node needs to be able to have 2 previews -->
                        <!--  1 preview to show or connect the inputs/outputs of nodes (for workflow connecting) -->
                        <!--  2 preview to show the rendered preview of the html (for page design) -->
                        <BuilderItem :name="n.name" :icon="n.type" item="n" />
                    </li>
                </ul>
                <div>
                    <pre>{{ state }}</pre>
                </div>
            </div>
            <div
                id="drawflow"
                @drop="drop($event)"
                @dragover="allowDrop($event)"
                class="parent-drawflow min-h-64"
            >
            </div>
        </div>
    </div>
    </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Drawflow from 'drawflow'
import 'drawflow/dist/drawflow.min.css?inline'
import { onMounted, shallowRef, h, getCurrentInstance, render, readonly, ref } from 'vue'
import BuilderItem from '../../Builder/BuilderItem.vue';
import designer from "@/Builder/designer.js";

const listNodes = readonly([
    {
        name: 'Text',
        color: '#49494970',
        component: 'DebugText',
        type: 'text',
        input:3,
        output:1,
    },
    {
        name: 'Button',
        color: 'blue',
        component: 'DebugText',
        input:3,
        type: 'button',
        output:2,
    },
    {
        name: 'Textarea',
        color: '#ff9900',
        component: 'DebugText',
        type: 'textarea',
        input:1,
        output:0,
    },
])

const editor = shallowRef({})
const dialogVisible = ref(false)
const dialogData = ref({})
const Vue = { version: 3, h, render };
const internalInstance = getCurrentInstance()
internalInstance.appContext.app._context.config.globalProperties.$df = editor;

function exportEditor() {
    dialogData.value = editor.value.export();
    dialogVisible.value = true;
}

const drag = (ev) => {
    if (ev.type === "touchstart") {
        mobile_item_selec = ev.target.closest(".drag-drawflow").getAttribute('data-node');
    } else {
        ev.dataTransfer.setData("node", ev.target.getAttribute('data-node'));
    }
}
const drop = (ev) => {
    if (ev.type === "touchend") {
        var parentdrawflow = document.elementFromPoint( mobile_last_move.touches[0].clientX, mobile_last_move.touches[0].clientY).closest("#drawflow");
        if(parentdrawflow != null) {
            addNodeToDrawFlow(mobile_item_selec, mobile_last_move.touches[0].clientX, mobile_last_move.touches[0].clientY);
        }
        mobile_item_selec = '';
    } else {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("node");
        addNodeToDrawFlow(data, ev.clientX, ev.clientY);
    }

}
const allowDrop = (ev) => {
    ev.preventDefault();
}

let mobile_item_selec = '';
let mobile_last_move = null;
function positionMobile(ev) {
    mobile_last_move = ev;
}

function addNodeToDrawFlow(name, pos_x, pos_y) {
    pos_x = pos_x * ( editor.value.precanvas.clientWidth / (editor.value.precanvas.clientWidth * editor.value.zoom)) - (editor.value.precanvas.getBoundingClientRect().x * ( editor.value.precanvas.clientWidth / (editor.value.precanvas.clientWidth * editor.value.zoom)));
    pos_y = pos_y * ( editor.value.precanvas.clientHeight / (editor.value.precanvas.clientHeight * editor.value.zoom)) - (editor.value.precanvas.getBoundingClientRect().y * ( editor.value.precanvas.clientHeight / (editor.value.precanvas.clientHeight * editor.value.zoom)));
    const nodeSelected = listNodes.find(ele => ele.component == name);

    console.log('added node', editor.value.addNode(
        name,
        nodeSelected.input,
        nodeSelected.output,
        pos_x,
        pos_y,
        name,
        {
            nodeSelected
        },
        name,
        'vue'
    ));
}

const state = ref({
    selectedNode: null,
    selectedModule: null,
});

onMounted(() => {
    var elements = document.getElementsByClassName('drag-drawflow');
    for (var i = 0; i < elements.length; i++) {
        elements[i].addEventListener('touchend', drop, false);
        elements[i].addEventListener('touchmove', positionMobile, false);
        elements[i].addEventListener('touchstart', drag, false );
    }
    const id = document.getElementById("drawflow");
    editor.value = new Drawflow(id, Vue, internalInstance.appContext.app._context);
    Object.keys(designer).forEach((key) => {
        const props = Object.keys(designer[key].props).reduce((carry, prop) => {
            const defaultValue = designer?.[key]?.props?.[prop]?.default();

            return {
                ...carry,
                [prop]: defaultValue,
            };
        }, {});

        editor.value.registerNode(
            key,
            designer[key],
            {
                ...props,
                editor,
            },
        )
    });
    // editor.value.registerNode('DebugText', DebugText, {
    //     // Props are passed to the node on node creation
    //     // So these could be "defaults" for the node
    // });
    editor.value.start();

    editor.value.on('nodeSelected', (id) => {
        state.value.selectedNode = editor.value.getNodeFromId(id);
        state.value.selectedModule = editor.value.getModuleFromNodeId(id);
        console.log('nodeSelected', id, );
    });
});
</script>
