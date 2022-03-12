export default {
    _count: 0,
    initTableType() {
        this.$watch('query', () => {
            this.currentPage = 1
            this.dispatch('query', this.query)
        });

        this.$watch('perPage', () => {
            this.currentPage = 1
            this.dispatch('perPage', this.perPage)
        });


    },
    sortByColumn(column) {
        if (!this._isSortable(column)) {
            return;
        }

        this._setSort(column)
    },
    _recordsCount() {
        return this._count
    },
    _currentPageData() {
        return this._filteredAndPaginatedData()
    },
    _clonedData() {
        return this.data.slice()
    },
    _filteredData() {
        let clone = this._clonedData()

        if (this.query) {
            clone = clone.filter(row => this._matchesQuery(row))
        }

        if (this.customFilters.length) {
            for (let i = 0; i < this.customFilters.length; i++) {

                let column = this.customFilters[i].column;
                let query = this.customFilters[i].query;
                let dataType = this.customFilters[i].dataType;
                let operand = this.customFilters[i].operand;
                let multiple = this.customFilters[i].multiple;

                let cond = this._getCustomFilterCondition(dataType, operand, query, multiple)

                if (query || typeof query === 'boolean') {
                    clone = clone.filter(row => cond(row[column], query))
                }

            }
        }

        if (this.currentSort.column) {
            let dir = this.currentSort.asc ? 1 : -1;
            let column = this.currentSort.column
            let columnObj = this.tableDefinition.columns.find(c => c.handle === column)
            if (!columnObj) {
                columnObj = {
                    handle: column
                }
            }

            let valA;
            let valB;

            clone.sort((a, b) => {
                valA = this._getCellValueForSortAndFilter(a, columnObj)
                valB = this._getCellValueForSortAndFilter(b, columnObj)

                return valA > valB ? dir : -dir;
            })
        }

        this._count = clone.length

        return clone;
    },
    _getCustomFilterCondition(dataType, operand, query, multiple) {
        if (dataType === 'boolean') {
            return (value, query) => query ? value : !value
        }

        if (dataType === 'list') {
            if (multiple) {
                return (values, query) => {
                    for (let i = 0; i < values.length; i++) {
                        if (values[i].value === query) {
                            return true
                        }
                    }
                    return false
                }
            } else {
                return (value, query) => value.value === query
            }
        }

        // Range filter
        if (typeof query === "object") {
            return (value, query) => value >= query[0] && value <= query[1]
        }

        if (!operand) {
            return (value, query) => value === query
        }

        if (operand === '>') {
            return (value, query) => value > query
        }

        if (operand === '<') {
            return (value, query) => value < query
        }

        if (operand === '>=') {
            return (value, query) => value >= query
        }

        if (operand === '<=') {
            return (value, query) => value <= query
        }

        if (operand === 'LIKE') {
            return (value, query) => value.includes(query)
        }
    },
    _filteredAndPaginatedData() {
        return this._filteredData().splice((this.currentPage - 1) * this.perPage, this.perPage)
    },
    _matchesQuery(row) {
        let query = String(this.query).toLowerCase();

        for (let i = 0; i < this.filterableColumnsObjects.length; i++) {
            let column = this.filterableColumnsObjects[i];
            let value = this._getCellValueForSortAndFilter(row, column)
            value = String(value).toLowerCase()

            if (value.includes(query)) {
                return true
            }
        }

        return false
    },
    _isList(columnHandle) {
        return this.listColumns.includes(columnHandle)
    },
    _getCellValueForSortAndFilter(row, column) {
        if (row[column.handle] === null) {
            return '';
        }

        if (column && column.dataType === 'list') {
            return this._getListValuePresentation(row, column)
        }

        return column.dataType === 'number' ? parseFloat(row[column.handle]) : row[column.handle]
    },
}