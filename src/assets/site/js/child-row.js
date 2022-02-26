export default {
    openChildRows: [],
    toggleChildRow(rowId) {
        if (this.openChildRows.includes(rowId)) {
            this.openChildRows =  this.openChildRows.filter(row=> {
                return row !== rowId
            })
        } else {
            this.openChildRows.push(rowId)
        }
    },
    renderChildRow(row) {
        const template = this.options.childRowTemplate

        return this.renderTemplate(template, row)
    },
    childRowOpen(rowId) {
        return this.openChildRows.includes(rowId)
    },
    _colspan() {
        let colspan = this.renderableColumns.length

        // if (this.options.childRowTogglerEnabled) {
            colspan++
        // }

        if (this.options.components.includes('selectableRows')) {
            colspan++;
        }

        return colspan;
    }
}