import Vue from 'vue/dist/vue.min'

new Vue({
    el: "#tablecloth-app",
    delimiters: ['${', '}'],
    data() {
        return {
            ...Tablecloth,
            allUsers:Tablecloth.userGroups.length===0,
            childRowTableColumns: [],
            nestedFieldsMap: Tablecloth.nestedFieldsMap,
            columns: Tablecloth.columns.map(c => {
                c.visible = false
                c.uid = this.rand()

                return c
            }),
            reordering: false
        }
    },
    mounted() {

        setTimeout(() => {
            let tabs = ['#basics', '#columns', '#options', '#general-settings', '#advanced'];
            let hash = location.hash;
            let tabsToHide = tabs.filter(tab => (hash ? tab !== hash : tab !== '#basics'));

            tabsToHide.forEach(tab => {
                $(tab).addClass('hidden');
            })
        })

        if (this.isNew) {
            this.$watch('name', () => {
                this.tableHandle = this.name.charAt(0).toLowerCase() + this.name.slice(1).replaceAll(' ', '')
            });

            this.sectionId = this.sections[0].value
            this.$nextTick(() => {
                this.typeId = this.sections[0].entryTypes[0].value
            })
        }


        var self = this

        $("#dt-columns").sortable({
            start: function (e, ui) {
                // creates a temporary attribute on the element with the old index
                $(this).attr('data-previndex', ui.item.index());
            },
            update: function (e, ui) {
                // gets the new and old index then removes the temporary attribute
                var newIndex = ui.item.index();
                var oldIndex = $(this).attr('data-previndex');
                $(this).removeAttr('data-previndex');
                self.reordering = true
                self.columns.splice(newIndex, 0, self.columns.splice(oldIndex, 1)[0])
                self.$nextTick(() => {
                    self.reordering = false
                })
            }
        })

        $(document).on('change', ".lightswitch", (e) => {
            var el = $(e.target).find('input[type=hidden]').eq(0);
            if (el.length) {
                var name = el[0].name;
                var value = el[0].value;

                if (name === 'enableChildRows') {
                    this.enableChildRows = !!value;
                } else if (name === 'overrideGeneralSettings') {
                    this.overrideGeneralSettings = !!value;
                } else if (name==='allUsers') {
                    this.allUsers = !!value;
                }
            }
        });
    },
    methods: {
        updateHeading(index) {
            let column = this.columns[index]
            column.heading = this.fieldsMap[column.handle]
        },
        rand() {
            return Math.floor(Math.random() * 10000) + 1
        },
        toggleChildRowTemplate(e) {
            console.log(e.target.checked)
        },
        addColumn() {
            if (this.columns.length > 0) {
                this.columns[this.columns.length - 1].visible = false
            }

            this.columns.push({
                uid: this.rand(),
                handle: '',
                heading: '',
                sortable: true,
                filterable: true,
                hidden: false,
                visible: true,
                template: ''
            })
        },
        deleteColumn(index) {
            this.columns.splice(index, 1)
        },
        addColumnToSqlFilter(handle) {
            this.datasetPrefilter += `{{${handle}}}`
            this.$refs.prefilter.focus()
        },
        getSource(source) {
            if (source === 'Product') {
                return 'craft\\commerce\\elements\\Product'
            }

            return 'craft\\elements\\' + source
        }
    },
    computed: {
        sourceName() {
            return this.source.split('\\').pop();
        },
        types() {
            if (this.source === this.getSource('Product')) {
                return this.productTypes;
            }

            if (!this.sectionId) {
                return []
            }

            let section = this.sections.find(section => parseInt(section.value) === parseInt(this.sectionId));

            return section ? section.entryTypes : []
        },
        selectedType() {
          return this.typeId ? this.types.find(type=>parseInt(type.value)===parseInt(this.typeId)) : {}
        },
        columnHandles() {
            return Object.keys(this.fieldsMap)
        }
    }
})