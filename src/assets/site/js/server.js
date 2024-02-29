const axios = require('axios')

export default {
    triggerPageWatcher: true,
    async initTableType() {
        this.$watch('currentPage', () => {
            if (this.triggerPageWatcher) {
                this.getData();
            }
        })

        this.$watch('query', () => {
            this.dispatch('query', this.query)
            this.getData(true);
        })


        this.$watch('perPage', () => {
            this.dispatch('perPage', this.perPage)
            this.getData(true);
        })

        const {data} = await axios.post(window.CraftTablecloth.cpUrl + `?action=tablecloth/site-data/get-count&handle=${this.handle}&siteId=${this.tableDefinition.siteId}`);
        this.totalCount = data

    },
    sortByColumn(column) {
        if (!this._isSortable(column)) {
            return;
        }

        this._setSort(column)

        this.getData();
    },
    _recordsCount() {
        return this.totalCount
    },
    _currentPageData() {
        return this.data;
    },
    async getData(resetPagination = false) {
        this.dispatch('loading')

        if (resetPagination) {
            this.triggerPageWatcher = false
            this.currentPage = 1
            this.$nextTick(() => {
                this.triggerPageWatcher = true
            })
        }

        let sortColumn = this.currentSort.column;
        let sortDirection = this.currentSort.asc ? 'ASC' : 'DESC';
        let query = this.query
        let perPage = this.perPage

        let params = `handle=${this.handle}&p=${this.currentPage}&siteId=${this.tableDefinition.siteId}`;

        if (sortColumn) {
            params += `&sortColumn=${sortColumn}&sortDirection=${sortDirection}`;
        }

        if (query) {
            params += `&q=${query}`
        }

        if (perPage) {
            params += `&perPage=${perPage}`
        }

        if (this.customFilters) {
            params += '&' + this._serialize(this._customFiltersMap(), 'filters')
        }

        let dataRes = await axios.post(window.CraftTablecloth.cpUrl + `?action=tablecloth/site-data/get-data&${params}`);
        this.data = this._transformDataset(dataRes.data)
        this.dispatch('loaded.data', this.data)

        let countParams = `handle=${this.handle}&siteId=${this.tableDefinition.siteId}`;

        if (query) {
            countParams += `&q=${query}`
        }

        if (this.customFilters) {
            countParams += '&' + this._serialize(this._customFiltersMap(), 'filters')
        }

        let countRes = await axios.post(window.CraftTablecloth.cpUrl + `?action=tablecloth/site-data/get-count&${countParams}`);
        this.totalCount = countRes.data
        this.dispatch('loaded.count', this.totalCount)

    },
    // on the server component we send the original handle
    // and then join the label on the server-side
    _getListColumnHandleForQuery(columnHandle) {
        return columnHandle;
    },
    _customFiltersMap() {
        let res = {}
        let operand;
        let handle;
        this.customFilters.forEach(f => {
            operand = f.operand ? f.operand : '';
            handle = f.column;
            if (f.operand) {
                handle += `_*_${operand}`
            }

            res[handle] = f.query
        })

        return res
    },
    _serialize(obj, prefix) {
        let str = [],
            p;
        for (p in obj) {
            if (obj.hasOwnProperty(p)) {
                var k = prefix ? prefix + "[" + p + "]" : p,
                    v = obj[p];
                str.push((v !== null && typeof v === "object") ?
                    this._serialize(v, k) :
                    encodeURIComponent(k) + "=" + encodeURIComponent(v));
            }
        }
        return str.join("&");
    }
}