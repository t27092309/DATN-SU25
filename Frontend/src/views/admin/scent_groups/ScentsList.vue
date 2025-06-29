<template>
    <div class="container">
        <div class="page-inner">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Add Row</h4>
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                            data-bs-target="#addRowModal">
                            <i class="fa fa-plus"></i>
                            Add Row
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Modal -->
                    <div class="modal fade" id="addRowModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">

                                <form @submit.prevent="addScents">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title">
                                            <span class="fw-mediumbold">Add</span>
                                            <span class="fw-light"> Scent_Groups </span>
                                        </h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- <p class="small">
                                        Create a new row using this form, make sure you
                                        fill them all
                                    </p> -->
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group form-group-default">
                                                    <label>Name</label>
                                                    <input id="addName" type="text" class="form-control"
                                                        placeholder="fill name" v-model="scent_group.name" />
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group form-group-default">
                                                    <label for="colorPicker">Color</label>
                                                    <input type="color" class="mt-2" id="colorPicker" name="color"
                                                        value="#00000" title="Chọn màu"
                                                        v-model="scent_group.color_code">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="submit" id="addRowButton" class="btn btn-primary">
                                            Add
                                        </button>
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                            Close
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="add-row" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Name</th>
                                    <th>Color</th>
                                    <th style="width: 15%">Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>STT</th>
                                    <th>Name</th>
                                    <th>Color</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <tr v-for="(scent, index) in scent_groups" :key="scent.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ scent.name }}</td>
                                    <td>
                                        <span class="colorinput-color"
                                            :style="{ backgroundColor: scent.color_code }"></span>
                                    </td>
                                    <td>
                                        <div class="form-button-action">
                                            <button type="button" data-bs-toggle="modal"
                                                @click="fetchScentsByID(scent.id)" data-bs-target="#updateRowModal"
                                                title="" class="btn btn-link btn-primary btn-lg"
                                                data-original-title="Edit Task">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button data-bs-toggle="tooltip" @click="deleteScents(scent.id)"
                                                class="btn btn-link btn-danger" data-original-title="Remove">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Modal update -->
                    <div class="modal fade" id="updateRowModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form @submit.prevent="updateScents(scent_group_byID.id)">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title">
                                            <span class="fw-mediumbold">Update</span>
                                            <span class="fw-light"> Scent_Groups </span>
                                        </h5>
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- <p class="small">
                                        Create a new row using this form, make sure you
                                        fill them all
                                    </p> -->

                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group form-group-default">
                                                    <label>Name</label>
                                                    <input id="addName" type="text" class="form-control"
                                                        placeholder="fill name" v-model="scent_group_byID.name" />
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group form-group-default">
                                                    <label>Color</label>
                                                    <input type="color" class="mt-2" id="colorPicker" name="color"
                                                        value="#0000" title="Chọn màu"
                                                        v-model="scent_group_byID.color_code">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="submit" id="addRowButton" class="btn btn-warning">
                                            Update
                                        </button>
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                            Close
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
    import Swal from 'sweetalert2';
    import { onMounted, ref } from 'vue';
    import { useRoute } from 'vue-router';
    import axios from 'axios'

    const router = useRoute()
    const brands = ref([]);
    const scent_groups = ref([]);
    const scent_group = ref({
        name: '',
        color_code: ''
    });
    const scent_group_byID = ref({
        name: '',
        color_code: ''
    });
    const { params } = useRoute();

    const fetchScents = async () => {
        try {
            const { data } = await axios.get(`http://127.0.0.1:8000/api/scent-groups`)
            scent_groups.value = data
        } catch (error) {
            alert('Co loi xay ra: ' + error.message)
        }
    }

    const fetchScentsByID = async (id) => {
        try {
            const { data } = await axios.get(`http://127.0.0.1:8000/api/scent-groups/${id}`)
            scent_group_byID.value = data
        } catch (error) {
            alert('Co loi xay ra: ' + error.message)
        }
    }

    const addScents = async () => {
        try {
            await axios.post('http://127.0.0.1:8000/api/scent-groups', scent_group.value)
            const result = await Swal.fire({
                title: 'Thêm thành công!',
                text: 'Chúc mừng, bạn đã thêm thành công!',
                icon: 'success',
                confirmButtonText: 'Tuyệt vời!'
            });

            // Code sẽ tạm dừng ở dòng "await" cho đến khi người dùng bấm nút
            if (result.isConfirmed) {
                window.location.reload();
            }
        } catch (error) {
            if (error.response && error.response.status === 422) {
                console.log("💥 Lỗi từ Laravel:", error.response.data.errors);
                alert("❌ Lỗi: " + JSON.stringify(error.response.data.errors, null, 2));
            } else {
                console.log("❌ Lỗi khác:", error.message);
            }
        }
    }

    const updateScents = async (id) => {
        try {
            await axios.put(`http://127.0.0.1:8000/api/scent-groups/${id}`, scent_group_byID.value)
            Swal.fire({
                title: 'Update thành công!',
                text: 'Chúc mừng, bạn đã update thành công!',
                icon: 'success', // 'success', 'error', 'warning', 'info', 'question'
                confirmButtonText: 'Tuyệt vời!',
            });
            window.location.reload();
        } catch (error) {
            if (error.response && error.response.status === 422) {
                console.log("💥 Lỗi từ Laravel:", error.response.data.errors);
                alert("❌ Lỗi: " + JSON.stringify(error.response.data.errors, null, 2));
            } else {
                console.log("❌ Lỗi khác:", error.message);
            }
        }
    }

    const deleteScents = async (id) => {
        try {
            const confirmDelete = await Swal.fire({
                title: 'Bạn có chắc muốn xóa ?',
                text: 'Bạn sẽ không thể hoàn tác hành động này!',
                icon: 'warning', // Dùng icon 'warning' cho hành động xóa sẽ hợp lý hơn
                showCancelButton: true, // Hiển thị nút "Hủy"
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Vâng, xóa đi!',
                cancelButtonText: 'Hủy' // Thêm text cho nút hủy
            });

            if (confirmDelete.isConfirmed) {
                await axios.delete(`http://127.0.0.1:8000/api/scent-groups/${id}`)
                fetchScents();
                Swal.fire({
                    title: 'Xóa thành công!',
                    text: 'Chúc mừng, bạn đã xóa thành công!',
                    icon: 'success', // 'success', 'error', 'warning', 'info', 'question'
                    confirmButtonText: 'Tuyệt vời!'
                });
            }
        } catch (error) {
            if (error.response) {
                console.log('Lỗi chi tiết:', error.response.data)
                alert('❌ Server báo lỗi: ' + JSON.stringify(error.response.data))
            } else {
                alert('❌ Không kết nối được tới server')
            }
        }
    }

    onMounted(() => {
        fetchScents()
    })





</script>

<style scoped>

    .custom-hover-link {
        color: #198754;
    }

    .custom-hover-link:hover {
        color: white !important;
    }
</style>