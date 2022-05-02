const axios = require('axios')

import client from './client'
import server from './server'
import childRow from './child-row'
import {format} from "date-fns"

const MOBILE_BREAKPOINT = 600;
let isMobile = window.outerWidth < MOBILE_BREAKPOINT;

window.tablecloth = function (handle) {
    let jsData = window[`CraftTablecloth_${handle}`];
    let isServerTable = jsData.serverTable;
    let siteId = jsData.siteId;
    let funcs = isServerTable ? server : client;

    return {
        async init() {
            this.loading = true
            this.dispatch('init.loading');
            const {data} = await axios.get(`/?action=tablecloth/site-data/get-initial-data&handle=${handle}&siteId=${siteId}`);
            this.dispatch('init.loaded', data);

            this.data = this._transformDataset(data)

            this.$nextTick(() => {
                this.loading = false
            });
            this.$watch('selectedRows', () => {
                this.dispatch('rows-select', this.selectedRows)
            });

            window.addEventListener('resize', () => {
                this.isMobile = window.outerWidth < MOBILE_BREAKPOINT;
            });

            window.addEventListener(`tablecloth.${this.handle}.filter.resetAll`, () => {
                this.customFilters = []
                this.currentPage = 1

                if (isServerTable) {
                    this.getData(true)
                }
            })

            window.addEventListener(`tablecloth.${this.handle}.filter.reset`, ({detail}) => {
                this.customFilters = this.customFilters.filter(f=>f.column!==detail.column)
                this.currentPage = 1

                if (isServerTable) {
                    this.getData(true)
                }
            })

            window.addEventListener(`tablecloth.${this.handle}.filter.setAll`, ({detail}) => {
                detail = detail.map(d => {
                    let dataType = this.tableDefinition.columns.find(col => col.handle === d.column).dataType
                    return {
                        ...d,
                        dataType
                    }
                })

                this.customFilters = detail
                this.currentPage = 1

                if (isServerTable) {
                    this.getData(true)
                }
            })

            window.addEventListener(`tablecloth.${this.handle}.filter`, ({detail}) => {
                let column = detail.column
                let col = this.tableDefinition.columns.find(col => col.handle === column)
                let dataType = col.dataType
                let multiple = col.multiple
                let data = {...detail, dataType, multiple};

                let existing = this.customFilters.find(filter => filter.column === column)
                if (existing) {
                    existing.query = detail.query
                } else {
                    this.customFilters.push(data)
                }

                this.currentPage = 1

                if (isServerTable) {
                    this.getData(true)
                }
            })

            window.addEventListener(`tablecloth.${this.handle}.sort`,({detail}) => {
                this.currentSort = detail
            })

            window.addEventListener(`tablecloth.${this.handle}.paginate`,({detail}) => {
                this.currentPage = detail
            })

            this.initTableType()
        },
        ...funcs,
        ...childRow,
        isMobile: isMobile,
        tableDefinition: window[`CraftTablecloth_${handle}`],
        data: [],
        customFilters: [],
        handle,
        initialized: false,
        tableHandle: '',
        perPage: String(window[`CraftTablecloth_${handle}`].options.initialPerPage),
        totalCount: 0,
        currentPage: 1,
        currentSort: {
            column: window[`CraftTablecloth_${handle}`].options.initialSortColumn,
            asc: window[`CraftTablecloth_${handle}`].options.initialSortAsc
        },
        query: '',
        selectedRows: [],
        visibleColumns: window[`CraftTablecloth_${handle}`].columns.filter(column => !column.hidden).map(column => column.handle),
        loading: false,
        open: false,
        // Computed Properties
        get recordsCount() {
            return this._recordsCount()
        },
        get paginationChunk() {
            return this.options.paginationChunk
        },
        get options() {
            return this.tableDefinition.options;
        },
        get lists() {
            return this.tableDefinition.lists;
        },
        get pages() {
            return Math.ceil(this.recordsCount / this.perPage);
        },
        get renderablePages() {
            return _range(this.paginationStart, this.pagesInCurrentChunk);
        },
        get totalChunks() {
            return Math.ceil(this.pages / this.paginationChunk);
        },
        get currentChunk() {
            return Math.ceil(this.currentPage / this.paginationChunk);
        },
        get paginationStart() {
            return ((this.currentChunk - 1) * this.paginationChunk) + 1;
        },
        get currentPageRange() {
            let start = (this.currentPage-1) * this.perPage + 1
            let end = Number(start) + Number(this.perPage) - 1

            return {
                start,
                end: end > this.recordsCount ? this.recordsCount : end
            }
        },
        get pagesInCurrentChunk() {
            return this.paginationStart + this.paginationChunk <= this.pages ?
                this.paginationChunk :
                this.pages - this.paginationStart + 1;

        },
        get allColumns() {
            return this.tableDefinition.columns
                .slice()
                .map(column => column.handle)
        },
        get tableFieldsLists() {
            let res = {}
            let optsMap = {}

            this.tableColumnsObjects.forEach(table => {
                (Object.values(table.columns))
                    .filter(column => column.type === 'select')
                    .forEach(column => {
                        column.options.forEach(col => {
                            optsMap[col.value] = col.label
                        })

                        res[`${table.handle}__${column.handle}`] = optsMap;
                    });
            });

            return res;

        },
        get tableColumnsObjects() {
            return Object.values(this.tableDefinition.columns
                .slice()
                .filter(column => column.fieldType === 'Table'))
        },
        get filterableColumnsObjects() {
            return this.tableDefinition.columns
                .slice()
                .filter(column => column.filterable)
        },
        get filterableColumns() {
            return this.tableDefinition.columns
                .slice()
                .filter(column => column.filterable)
                .map(column => column.handle)
        },
        get hiddenColumns() {
            return this.tableDefinition.columns
                .slice()
                .filter(column => column.hidden)
                .map(column => column.handle)
        },
        get displayableColumns() {
            return this.tableDefinition.columns.slice().filter(column =>
                !this.hiddenColumns.includes(column.handle)
            );
        },
        get sortableColumns() {
            return this.tableDefinition.columns
                .slice()
                .filter(column => column.sortable)
                .map(column => column.handle)
        },
        get renderableColumns() {
            return this.displayableColumns.slice().filter(column =>
                this.visibleColumns.includes(column.handle)
            );
        },
        get dateColumns() {
            return this._getColumnsByDataType('date');
        },
        get listColumns() {
            return this._getColumnsByDataType('list');
        },
        get customListColumns() {
            return this._getColumnsByDataType('list', 'custom', null, ['MultiSelect', 'Categories', 'Tags', 'Checkboxes'])
        },
        get relationColumns() {
            return this._getColumnsByDataType('list', 'custom', ['Categories', 'Tags', 'Entries', 'Users'])
        },
        get multiselectColumns() {
            return this._getColumnsByDataType('list', 'custom', ['MultiSelect', 'Checkboxes'])
        },
        get booleanColumns() {
            return this._getColumnsByDataType('boolean')
        },
        get entriesColumns() {
            return this._getColumnsByDataType('list', 'custom', ['Entries'])
        },
        get currentPageData() {
            return this._currentPageData()
        },
        get colspan() {
            return this._colspan()
        },
        get perPageValues() {
            return this.options.perPageValues
        },
        get childRowSource() {
            return this.options.childRowSource
        },
        // Methods
        componentEnabled(component) {
            return this.options.components.includes(component)
        },
        renderTemplate(template, row) {
            let regex = /\[\[(.+?)\]\]/g
            let match;
            let variable;
            let handle;

            while (match = regex.exec(template)) {
                variable = match[0];
                handle = match[1];

                template = template.replace(variable, row[handle])
            }

            return template;
        },
        sortSuffix(column) {
            if (this.currentSort.column === column) {
                return this.currentSort.asc ? '-up' : '-down';
            }

            return '';
        },
        setPage(page) {
            this.currentPage = page
            this.dispatch('paginate', page)
        },
        nextPage() {
            if (this.currentPage < this.pages) {
                this.setPage(this.currentPage + 1)
            }
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.setPage(this.currentPage - 1)
            }
        },
        selectAllCheckbox($event) {

            this.selectedRows = [];

            if ($event.target.checked) {
                this.data.forEach(row => {
                    this.selectedRows.push(row.id)
                })
            }
        },
        // For columns without template
        getCellValue(row, column) {
            if (row[column.handle] === null) {
                return ''
            }

            if (column.dataType === 'date') {
                return row[column.handle] ? format(row[column.handle], this.options.dateFormat) : ''
            }

            if (column.dataType === 'list') {
                return this._getListValuePresentation(row, column)
            }

            if (column.dataType === 'number') {
                return !isNaN(row[column.handle]) ? this.numberFormat(row[column.handle]) : ''
            }

            return row[column.handle]
        },
        dateFormat(val, dateFormat = null) {
            if (!dateFormat) {
                dateFormat = this.options.dateFormat
            }

            return format(new Date(val), dateFormat)
        },
        timeFormat(val, timeFormat = null) {
            if (!timeFormat) {
                timeFormat = this.options.timeFormat
            }

            return format(new Date('2021-01-01 ' + val), timeFormat)
        },
        numberFormat(val, locale = null, options = null) {
            if (locale && options) {
                return new Intl.NumberFormat(locale, options).format(val)
            }

            return new Intl.NumberFormat().format(val)
        },
        _getListValuePresentation(row, column) {
            if (column.multiple) {
                return this._getPresentationForMultipleList(column, row[column.handle]);
            } else {
                return row[column.handle].label
            }
        },
        dispatch(event, data) {
            this.$dispatch(`tablecloth.${this.handle}.${event}`, data)
        },
        _isSortable(column) {
            return this.sortableColumns.includes(column)
        },
        _setSort(column) {
            if (this.currentSort.column === column) {
                this.currentSort.asc = !this.currentSort.asc
            } else {
                this.currentSort = {
                    column: column,
                    asc: true
                }
            }

            this.dispatch('sort', this.currentSort)
        },
        _getColumnsByDataType(dataType, category = null, fieldTypes = null, exclude = []) {
            return this.tableDefinition.columns
                .slice()
                .filter(column => column.dataType === dataType &&
                    (!category || column.type === category) &&
                    (!fieldTypes || fieldTypes.includes(column.fieldType)) &&
                    !exclude.includes(column.fieldType))
                .map(column => column.handle)
        },
        _transformDataset(data) {
            if (this.dateColumns.length > 0) {
                data = data.map(row => {
                    this.dateColumns.forEach(col => {
                        row[col] = row[col] ? new Date(row[col]) : ''
                    })

                    return row;
                })
            }

            return data
        },
        _getPresentationForMultipleList(column, value) {
            if (this.relationColumns.includes(column.handle)) {
                if (column.fieldType === 'Users') {
                    return value.map(user => user.data.fullName ? user.data.fullName : user.data.username).join(', ')
                }

                return value.map(row => row.data.title).join(', ')
            } else {
                return value.map(row => row.label).join(', ')
            }
        }
    }
}

function _range(start, count) {
    return Array.apply(0, Array(count))
        .map(function (element, index) {
            return index + start;
        });
}

